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

use Magento\AggregatedServices\Api\Data\AttributeInformationInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * @inheritdoc
 */
class AttributeInformation extends AbstractSimpleObject implements AttributeInformationInterface
{
    /**
     * @inheritdoc
     */
    public function setCode($code)
    {
        return $this->setData('code', $code);
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->_get('code');
    }
    /**
     * @inheritdoc
     */
    public function setLabel($label)
    {
        return $this->setData('label', $label);
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->_get('label');
    }

    /**
     * @inheritdoc
     */
    public function setOptions($options = null)
    {
        return $this->setData('options', $options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return $this->_get('options');
    }
}
