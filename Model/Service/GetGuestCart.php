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
use Magento\AggregatedServices\Api\Service\GetCartInterface as GetAggregatedCartInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;

/**
 * @inheritdoc
 */
class GetGuestCart implements GetGuestCartInterface
{
    /**
     * @var GetAggregatedCartInterface
     */
    private $getAggregatedCartService;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskResource;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GetAggregatedCartInterface $getAggregatedCartService
     * @param QuoteIdMaskResource $quoteIdMaskResource
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        GetAggregatedCartInterface $getAggregatedCartService,
        QuoteIdMaskResource $quoteIdMaskResource

    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->getAggregatedCartService = $getAggregatedCartService;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
    }

    /**
     * @inheritdoc
     */
    public function get(
        $cartId,
        \Magento\Framework\Api\SearchCriteriaInterface $productAttributesSearchCriteria = null
    ) {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $cartId, 'masked_id');

        return $this->getAggregatedCartService->get($quoteIdMask->getQuoteId(), $productAttributesSearchCriteria);
    }
}
