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

namespace Magento\AggregatedServices\Model\Data;

use Magento\AggregatedServices\Api\Data\AggregatedCartInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * @inheritdoc
 */
class AggregatedCart extends AbstractExtensibleObject implements AggregatedCartInterface
{
    public function getCartDetails()
    {
        return $this->_get('cart_details');
    }

    public function setCartDetails(\Magento\Quote\Api\Data\CartInterface $cartDetails)
    {
        return $this->setData('cart_details', $cartDetails);
    }

    public function getPaymentMethod()
    {
        return $this->_get('payment_method');
    }

    public function setPaymentMethod(\Magento\Quote\Api\Data\PaymentInterface $paymentMethod)
    {
        return $this->setData('payment_method', $paymentMethod);
    }

    public function getTotals()
    {
        return $this->_get('totals');
    }

    public function setTotals(\Magento\Quote\Api\Data\TotalsInterface $totals)
    {
        return $this->setData('totals', $totals);
    }

    public function getProducts()
    {
        return $this->_get('products');
    }

    public function setProducts(\Magento\Framework\Api\SearchResultsInterface $products)
    {
        return $this->setData('products', $products);
    }

    public function getProductAttributes()
    {
        return $this->_get('product_attributes');
    }

    public function getConfigurableParentRelations()
    {
        return $this->_get('configurable_parent_relations');
    }

    public function setConfigurableParentRelations($configurableParentRelations)
    {
        return $this->setData('configurable_parent_relations', $configurableParentRelations);
    }


    public function setProductAttributes(
        $productAttributes
    ) {
        return $this->setData('product_attributes', $productAttributes);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        \Magento\AggregatedServices\Api\Data\AggregatedCartExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
