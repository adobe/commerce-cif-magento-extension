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

/**
 * Interface for getting aggregated cart data in one call.
 */
interface GetCartInterface
{
    /**
     * Get aggregated cart data in one call.
     *
     * @see \Magento\Quote\Api\CartRepositoryInterface::get
     * @see \Magento\Quote\Api\PaymentMethodManagementInterface::get
     * @see \Magento\Quote\Api\CartTotalRepositoryInterface::get
     * @see \Magento\Catalog\Api\ProductRepositoryInterface::getList
     * @see \Magento\Catalog\Api\ProductAttributeRepositoryInterface::getList
     *
     * @param string $cartId The cart ID.
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $productAttributesSearchCriteria
     * @return \Magento\AggregatedServices\Api\Data\AggregatedCartInterface Aggregated cart data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get(
        $cartId,
        \Magento\Framework\Api\SearchCriteriaInterface $productAttributesSearchCriteria = null
    );
}
