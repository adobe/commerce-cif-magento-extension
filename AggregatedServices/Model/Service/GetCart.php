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

namespace Magento\AggregatedServices\Model\Service;

use Magento\AggregatedServices\Api\Service\GetCartInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProductTypeResource;
use Magento\AggregatedServices\Api\Data\ConfigurableParentRelationInterface;
use Magento\AggregatedServices\Api\Data\ConfigurableParentRelationInterfaceFactory;
use Magento\AggregatedServices\Api\Data\AttributeInformationInterfaceFactory;
use Magento\AggregatedServices\Api\Data\AttributeOptionInformationInterfaceFactory;
use Magento\AggregatedServices\Api\Data\AttributeInformationInterface;

/**
 * @inheritdoc
 */
class GetCart implements GetCartInterface
{
    /**
     * @var \Magento\AggregatedServices\Api\Data\AggregatedCartInterfaceFactory
     */
    private $aggregatedCartFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    private $cartTotalRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ConfigurableProductTypeResource
     */
    private $configurableProductTypeResource;
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ConfigurableParentRelationInterfaceFactory
     */
    private $configurableParentRelationInterfaceFactory;

    /**
     * @var AttributeOptionInformationInterfaceFactory
     */
    private $attributeInformationFactory;

    /**
     * @var AttributeOptionInformationInterfaceFactory
     */
    private $attributeOptionInformationFactory;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\AggregatedServices\Api\Data\AggregatedCartInterfaceFactory $aggregatedCartFactory
     * @param ConfigurableParentRelationInterfaceFactory $configurableParentRelationInterfaceFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ConfigurableProductTypeResource $configurableProductTypeResource
     * @param ProductFactory $productFactory
     * @param AttributeInformationInterfaceFactory $attributeInformationFactory
     * @param AttributeOptionInformationInterfaceFactory $attributeOptionInformationFactory
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\AggregatedServices\Api\Data\AggregatedCartInterfaceFactory $aggregatedCartFactory,
        ConfigurableParentRelationInterfaceFactory $configurableParentRelationInterfaceFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        ConfigurableProductTypeResource $configurableProductTypeResource,
        ProductFactory $productFactory,
        AttributeInformationInterfaceFactory $attributeInformationFactory,
        AttributeOptionInformationInterfaceFactory $attributeOptionInformationFactory
    ) {
        $this->aggregatedCartFactory = $aggregatedCartFactory;
        $this->cartRepository = $cartRepository;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->productRepository = $productRepository;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->configurableProductTypeResource = $configurableProductTypeResource;
        $this->productFactory = $productFactory;
        $this->configurableParentRelationInterfaceFactory = $configurableParentRelationInterfaceFactory;
        $this->attributeInformationFactory = $attributeInformationFactory;
        $this->attributeOptionInformationFactory = $attributeOptionInformationFactory;
    }

    /**
     * @inheritdoc
     */
    public function get(
        $cartId,
        \Magento\Framework\Api\SearchCriteriaInterface $productAttributesSearchCriteria = null
    ) {
        /** @var \Magento\AggregatedServices\Api\Data\AggregatedCartInterface $aggregatedCart */
        $aggregatedCart = $this->aggregatedCartFactory->create();

        $cartDetails = $this->cartRepository->get($cartId);
        $aggregatedCart->setCartDetails($cartDetails);

        // Get parent product SKU for configurable items
        $configurableParentRelations = [];
        foreach ($cartDetails->getItems() as $cartItem) {
            if ($cartItem->getProductType() == 'configurable') {
                /** @var ConfigurableParentRelationInterface $configurableParentRelation */
                $configurableParentRelation = $this->configurableParentRelationInterfaceFactory->create();
                $configurableParentRelation->setVariantSku($cartItem->getSku());

                $configurableProductVariant = $this->productRepository->get(
                    $configurableParentRelation->getVariantSku()
                );
                $parentIds = $this->configurableProductTypeResource->getParentIdsByChild(
                    $configurableProductVariant->getId()
                );
                if (isset($parentIds[0])) {
                    $configurableProductParent = $this->productRepository->getById($parentIds[0]);
                    $configurableParentRelation->setParentSku($configurableProductParent->getSku());
                }
                $configurableParentRelations[] = $configurableParentRelation;
            }
        }
        $aggregatedCart->setConfigurableParentRelations($configurableParentRelations);

        $paymentMethod = $this->paymentMethodManagement->get($cartId);
        if ($paymentMethod) {
            $aggregatedCart->setPaymentMethod($paymentMethod);
        }
        $aggregatedCart->setTotals($this->cartTotalRepository->get($cartId));

        // Fetch cart products
        $productSkus = [];
        foreach ($cartDetails->getItems() as $cartItem) {
            $productSkus[] = $cartItem->getSku();
        }
        $productsSearchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $productSkus, 'in')
            ->create();
        $aggregatedCart->setProducts($this->productRepository->getList($productsSearchCriteria));

        // Fetch attributes metadata
        if ($productAttributesSearchCriteria) {
            $aggregatedCart->setProductAttributes($this->getAttributesInformation($productAttributesSearchCriteria));
        }

        return $aggregatedCart;
    }

    /**
     * Get attributes information
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $productAttributesSearchCriteria
     * @return AttributeInformationInterface[] $attributes
     */
    private function getAttributesInformation(
        \Magento\Framework\Api\SearchCriteriaInterface $productAttributesSearchCriteria
    ) {
        $productAttributes = $this->productAttributeRepository->getList($productAttributesSearchCriteria);
        $attributes = null;
        foreach ($productAttributes->getItems() as $productAttribute) {
            $options = null;
            if (is_array($productAttribute->getOptions())) {
                foreach ($productAttribute->getOptions() as $option) {
                    $options[] = $this->attributeOptionInformationFactory->create(
                        [
                            'data' => [
                                'value' => $option->getValue(),
                                'label' => $option->getLabel(),
                            ]
                        ]
                    );
                }
            }
            $attributes[] = $this->attributeInformationFactory->create(
                [
                    'data' => [
                        'code' => $productAttribute->getAttributeCode(),
                        'label' => $productAttribute->getFrontendLabel(),
                        'options' => $options,
                    ]
                ]
            );
        }
        return $attributes;
    }
}
