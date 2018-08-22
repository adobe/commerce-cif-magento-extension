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

use Magento\AggregatedServices\Api\Service\MergeCartsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;

/**
 * @inheritdoc
 */
class MergeCarts implements MergeCartsInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResourceModel
     */
    private $quoteResourceModel;

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
     * @param QuoteIdMaskResource $quoteIdMaskResource
     * @param QuoteResourceModel $quoteResourceModel
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResource $quoteIdMaskResource,
        QuoteResourceModel $quoteResourceModel,
        QuoteFactory $quoteFactory

    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
        $this->quoteResourceModel = $quoteResourceModel;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @inheritdoc
     */
    public function mergeGuestCartIntoActiveCustomerCart(
        $customerCartId,
        $guestCartId
    ) {
        try {
            $customerQuote = $this->quoteFactory->create();
            $this->quoteResourceModel->load($customerQuote, $customerCartId);
            /** @var $guestQuoteIdMask QuoteIdMask */
            $guestQuoteIdMask = $this->quoteIdMaskFactory->create();
            $this->quoteIdMaskResource->load($guestQuoteIdMask, $guestCartId, 'masked_id');
            $guestQuote = $this->quoteFactory->create();
            $unmaskedGuestCartId = $guestQuoteIdMask->getQuoteId();
            $this->quoteResourceModel->load($guestQuote, $unmaskedGuestCartId);

            // Merge guest cart into customer cart
            $customerQuote->merge($guestQuote);
            $customerQuote->setTotalsCollectedFlag(false);
            $customerQuote->collectTotals();
            $this->quoteResourceModel->save($customerQuote);

            // Disable guest cart
            $guestQuote->setIsActive(false);
            $this->quoteResourceModel->save($guestQuote);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Unable to merge specified guest quote into active customer quote.'));
        }
        return $customerQuote->getId();
    }
}
