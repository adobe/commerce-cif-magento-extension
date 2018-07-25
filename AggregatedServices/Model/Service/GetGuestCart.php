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

use Magento\AggregatedServices\Api\Service\GetGuestCartInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProductTypeResource;
use Magento\AggregatedServices\Api\Data\ConfigurableParentRelationInterface;
use Magento\AggregatedServices\Api\Data\ConfigurableParentRelationInterfaceFactory;

/**
 * @inheritdoc
 */
class GetGuestCart implements GetGuestCartInterface
{
    /**
     * @var \Magento\AggregatedServices\Api\Data\AggregatedGuestCartInterfaceFactory
     */
    private $aggregatedGuestCartFactory;

    /**
     * @var \Magento\Quote\Api\GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @var \Magento\Quote\Api\GuestPaymentMethodManagementInterface
     */
    private $guestPaymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\GuestCartTotalRepositoryInterface
     */
    private $guestCartTotalRepository;

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
     * @param \Magento\Quote\Api\GuestCartRepositoryInterface $guestCartRepository
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface $guestPaymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartTotalRepositoryInterface $guestCartTotalRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\AggregatedServices\Api\Data\AggregatedGuestCartInterfaceFactory $aggregatedGuestCartFactory
     * @param ConfigurableParentRelationInterfaceFactory $configurableParentRelationInterfaceFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ConfigurableProductTypeResource $configurableProductTypeResource
     * @param ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Quote\Api\GuestCartRepositoryInterface $guestCartRepository,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $guestPaymentMethodManagement,
        \Magento\Quote\Api\GuestCartTotalRepositoryInterface $guestCartTotalRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\AggregatedServices\Api\Data\AggregatedGuestCartInterfaceFactory $aggregatedGuestCartFactory,
        ConfigurableParentRelationInterfaceFactory $configurableParentRelationInterfaceFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        ConfigurableProductTypeResource $configurableProductTypeResource,
        ProductFactory $productFactory
    ) {
        $this->aggregatedGuestCartFactory = $aggregatedGuestCartFactory;
        $this->guestCartRepository = $guestCartRepository;
        $this->guestPaymentMethodManagement = $guestPaymentMethodManagement;
        $this->guestCartTotalRepository = $guestCartTotalRepository;
        $this->productRepository = $productRepository;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->configurableProductTypeResource = $configurableProductTypeResource;
        $this->productFactory = $productFactory;
        $this->configurableParentRelationInterfaceFactory = $configurableParentRelationInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function get(
        $cartId,
        \Magento\Framework\Api\SearchCriteriaInterface $productAttributesSearchCriteria = null
    ) {
        /** @var \Magento\AggregatedServices\Api\Data\AggregatedGuestCartInterface $aggregatedGuestCart */
        $aggregatedGuestCart = $this->aggregatedGuestCartFactory->create();

        $cartDetails = $this->guestCartRepository->get($cartId);
        $aggregatedGuestCart->setCartDetails($cartDetails);

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
        $aggregatedGuestCart->setConfigurableParentRelations($configurableParentRelations);

        $paymentMethod = $this->guestPaymentMethodManagement->get($cartId);
        if ($paymentMethod) {
            $aggregatedGuestCart->setPaymentMethod($paymentMethod);
        }
        $aggregatedGuestCart->setTotals($this->guestCartTotalRepository->get($cartId));

        // Fetch cart products
        $productSkus = [];
        foreach ($cartDetails->getItems() as $cartItem) {
            $productSkus[] = $cartItem->getSku();
        }
        $productsSearchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $productSkus, 'in')
            ->create();
        $aggregatedGuestCart->setProducts($this->productRepository->getList($productsSearchCriteria));

        // Fetch cart products
        if ($productAttributesSearchCriteria) {
            $aggregatedGuestCart->setProductAttributes(
                $this->productAttributeRepository->getList($productAttributesSearchCriteria)
            );
        }

        return $aggregatedGuestCart;
    }
}
