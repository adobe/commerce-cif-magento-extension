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
namespace Magento\AggregatedServices\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Aggregated cart data.
 */
interface AggregatedCartInterface extends ExtensibleDataInterface
{
    /**
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getCartDetails();

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cartDetails
     * @return $this
     */
    public function setCartDetails(\Magento\Quote\Api\Data\CartInterface $cartDetails);
    
    /**
     * @return \Magento\Quote\Api\Data\PaymentInterface
     */
    public function getPaymentMethod();

    /**
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @return $this
     */
    public function setPaymentMethod(\Magento\Quote\Api\Data\PaymentInterface $paymentMethod);
    
    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function getTotals();

    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals
     * @return $this
     */
    public function setTotals(\Magento\Quote\Api\Data\TotalsInterface $totals);
    
    /**
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface|null
     */
    public function getProducts();

    /**
     * @param \Magento\Framework\Api\SearchResultsInterface $products
     * @return $this
     */
    public function setProducts(\Magento\Framework\Api\SearchResultsInterface $products);
    
    /**
     * @return \Magento\AggregatedServices\Api\Data\AttributeInformationInterface[]|null
     */
    public function getProductAttributes();

    /**
     * @param \Magento\AggregatedServices\Api\Data\AttributeInformationInterface[] $productAttributes
     * @return $this
     */
    public function setProductAttributes($productAttributes);

    /**
     * @return \Magento\AggregatedServices\Api\Data\ConfigurableParentRelationInterface[]|null
     */
    public function getConfigurableParentRelations();

    /**
     * @param \Magento\AggregatedServices\Api\Data\ConfigurableParentRelationInterface[] $configurableParentRelations
     * @return $this
     */
    public function setConfigurableParentRelations($configurableParentRelations);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\AggregatedServices\Api\Data\AggregatedCartExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\AggregatedServices\Api\Data\AggregatedCartExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\AggregatedServices\Api\Data\AggregatedCartExtensionInterface $extensionAttributes
    );
}
