<?php
/**
 * Product : B2bzanden Always Ship
 *
 * @copyright Copyright © 2020 B2bzanden. All rights reserved.
 * @author    Isolde van Oosterhout & Hans Kuijpers
 */

namespace B2bzanden\AlwaysShip\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_ENABLED = 'alwaysship/general/enabled';

    private $config;

    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    public function isEnabled()
    {
        return $this->config->getValue(self::XML_PATH_ENABLED);
    }
}
