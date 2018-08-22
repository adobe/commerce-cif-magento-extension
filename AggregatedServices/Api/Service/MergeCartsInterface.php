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
 * Interface for merging guest cart into the active cart of the customer.
 *
 * Scenario:
 * - Guest is adding items to cart
 * - Guest logs in and becomes a customer, which might have existing cart from previous session
 * - Guest cart needs to be merged into customer's existing cart
 */
interface MergeCartsInterface
{
    /**
     * Merge guest cart into active customer cart.
     *
     * @param int $customerCartId
     * @param string $guestCartId Guest cart ID hash
     * @return int Customer cart ID
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function mergeGuestCartIntoActiveCustomerCart(
        $customerCartId,
        $guestCartId
    );
}
