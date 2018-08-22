<?php
/*******************************************************************************
 *
 *    Copyright 2018 Adobe. All rights reserved.
 *    This file is licensed to you under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License. You may obtain a copy
 *    of the License at http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software distributed under
 *    the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR REPRESENTATIONS
 *    OF ANY KIND, either express or implied. See the License for the specific language
 *    governing permissions and limitations under the License.
 *
 ******************************************************************************/

declare(strict_types=1);

namespace Magento\AggregatedServices\Api\Service;

class MergeCartsTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const CUSTOMER_AGGREGATED_CART = '/V1/customer-aggregated-carts/mine';
    const MERGE_WITH_GUEST_CART = '/V1/carts/mine/merge-with-guest-cart';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Validate that positive scenario for merging guest cart into active customer cart works correctly.
     *
     * Tested scenario:
     *      1. Create customer cart with simple product (quantity 2)
     *      2. Create guest cart with virtual product
     *      3. Merge guest cart into customer cart
     *      4. Validate totals and items in the cart
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testGet()
    {
        $this->_markTestAsRestOnly();

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $customerToken = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        /** @var \Magento\Quote\Model\Quote $customerQuote */
        $customerQuote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $customerQuote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Quote\Model\Quote $guestQuote */
        $guestQuote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $guestQuote->load('test_order_with_virtual_product_without_address', 'reserved_order_id');
        $hashedCartId = $this->getMaskedCartId($guestQuote->getId());


        $mergeCartsResponse = $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => self::MERGE_WITH_GUEST_CART . '/' . $hashedCartId,
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
                    'token' => $customerToken
                ],
            ]
        );

        $this->assertEquals($customerQuote->getId(), $mergeCartsResponse);

        $getCustomerCartResponse = $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => self::CUSTOMER_AGGREGATED_CART,
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                    'token' => $customerToken
                ],
            ]
        );

        // Validate totals
        // After the merge there should be simple product (qty 2) in cart $10 each, and a virtual product priced at $10
        $this->assertArrayHasKey('totals', $getCustomerCartResponse);
        $this->assertArrayHasKey('grand_total', $getCustomerCartResponse['totals']);

        $this->assertEquals(30, $getCustomerCartResponse['totals']['grand_total']);
        $this->assertEquals(3, $getCustomerCartResponse['totals']['items_qty']);

        $this->assertCount(2, $getCustomerCartResponse['totals']['items']);

        $this->assertEquals('Simple Product', $getCustomerCartResponse['totals']['items'][0]['name']);
        $this->assertEquals(2, $getCustomerCartResponse['totals']['items'][0]['qty']);

        $this->assertEquals('Virtual Product', $getCustomerCartResponse['totals']['items'][1]['name']);
        $this->assertEquals(1, $getCustomerCartResponse['totals']['items'][1]['qty']);

        // Validate additional cart details
        $this->assertArrayHasKey('cart_details', $getCustomerCartResponse);
        $this->assertEquals(2, $getCustomerCartResponse['cart_details']['items_count']);
        $this->assertEquals(3, $getCustomerCartResponse['cart_details']['items_qty']);
        $this->assertEquals('customer@example.com', $getCustomerCartResponse['cart_details']['billing_address']['email']);
    }

    /**
     * Retrieve masked cart ID for guest cart.
     *
     * @param string $cartId
     * @return string
     */
    private function getMaskedCartId(string $cartId) : string
    {
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        return $quoteIdMask->getMaskedId();
    }
}
