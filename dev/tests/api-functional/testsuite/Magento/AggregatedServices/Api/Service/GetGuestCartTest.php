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

namespace Magento\AggregatedServices\Api;

class GetGuestCartTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const RESOURCE_PATH = '/V1/guest-aggregated-carts';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    protected function tearDown()
    {
        $this->deleteCart('test_order_1');
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_product_shipping_and_payment.php
     * @magentoConfigFixture default_store webapi/webapisecurity/allow_insecure 1
     */
    public function testGet()
    {
        $this->_markTestAsRestOnly();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $this->getMaskedCartId($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $cartId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        $actualCartDetails = $this->_webApiCall($serviceInfo);
        $expectedCartDetails = $this->getExpectedCartDetails($cartId);

        $this->assertEquals($expectedCartDetails, $actualCartDetails);
    }

    /**
     * @param string $cartId
     * @return array
     */
    private function getExpectedCartDetails(string $cartId) : array
    {
        $hydrator = $this->objectManager->create(
            \Magento\Framework\EntityManager\Hydrator::class
        );
        $guestCartRepository = $this->objectManager->create(
            \Magento\Quote\Api\GuestCartRepositoryInterface::class
        );
        $guestPaymentMethodManagement = $this->objectManager->create(
            \Magento\Quote\Api\GuestPaymentMethodManagementInterface::class
        );
        $guestCartTotalRepository = $this->objectManager->create(
            \Magento\Quote\Api\GuestCartTotalRepositoryInterface::class
        );
        $searchCriteriaBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $productRepository = $this->objectManager->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );

        $cart = $guestCartRepository->get($cartId);

        $productSkus = [];
        foreach ($cart->getItems() as $cartItem) {
            $productSkus[] = $cartItem->getSku();
        }

        $productsSearchCriteria = $searchCriteriaBuilder
            ->addFilter('sku', $productSkus, 'in')
            ->create();

        $expectedCartDetailsObject = $this->objectManager->create(
            \Magento\AggregatedServices\Api\Data\AggregatedGuestCartInterface::class,
            [
                'data' => [
                    'cart_details' => $cart,
                    'payment_method' => $guestPaymentMethodManagement->get($cartId),
                    'totals' => $guestCartTotalRepository->get($cartId),
                    'products' => $productRepository->getList($productsSearchCriteria),
                    'configurable_parent_relations' => $this->getConfigurableParentRelations($cart),
                ]

            ]
        );

        return $hydrator->extract($expectedCartDetailsObject);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cartDetails
     * @return array
     */
    private function getConfigurableParentRelations(
        \Magento\Quote\Api\Data\CartInterface $cartDetails
    ) : array {
        $configurableParentRelationInterfaceFactory = $this->objectManager->get(
            \Magento\AggregatedServices\Api\Data\ConfigurableParentRelationInterfaceFactory::class
        );
        $configurableProductTypeResource = $this->objectManager->get(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class
        );
        $productRepository = $this->objectManager->get(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );

        // Get parent product SKU for configurable items
        $configurableParentRelations = [];
        foreach ($cartDetails->getItems() as $cartItem) {
            if ($cartItem->getProductType() == 'configurable') {
                /** @var ConfigurableParentRelationInterface $configurableParentRelation */
                $configurableParentRelation = $configurableParentRelationInterfaceFactory->create();
                $configurableParentRelation->setVariantSku($cartItem->getSku());

                $configurableProductVariant = $productRepository->get(
                    $configurableParentRelation->getVariantSku()
                );
                $parentIds = $configurableProductTypeResource->getParentIdsByChild(
                    $configurableProductVariant->getId()
                );
                if (isset($parentIds[0])) {
                    $configurableProductParent = $productRepository->getById($parentIds[0]);
                    $configurableParentRelation->setParentSku($configurableProductParent->getSku());
                }
                $configurableParentRelations[] = $configurableParentRelation;
            }
        }
        return $configurableParentRelations;
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

    /**
     * Delete quote by given reserved order ID
     *
     * @param string $reservedOrderId
     * @return void
     */
    private function deleteCart(string $reservedOrderId)
    {
        try {
            /** @var $cart \Magento\Quote\Model\Quote */
            $cart = $this->objectManager->get(\Magento\Quote\Model\Quote::class);
            $cart->load($reservedOrderId, 'reserved_order_id');
            if (!$cart->getId()) {
                throw new \InvalidArgumentException('There is no quote with provided reserved order ID.');
            }
            $cart->delete();
            /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
            $quoteIdMask = $this->objectManager->create(\Magento\Quote\Model\QuoteIdMask::class);
            $quoteIdMask->load($cart->getId(), 'quote_id');
            $quoteIdMask->delete();
        } catch (\InvalidArgumentException $e) {
            // Do nothing if cart fixture was not used
        }
    }
}
