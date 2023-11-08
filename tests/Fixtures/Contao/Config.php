<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao;

/**
 * Simulate the contao config class.
 */
class Config
{
    /**
     * Object instance (Singleton)
     * @var \Config
     */
    protected static $objInstance;

    /**
     * Return the current object instance (Singleton)
     *
     * @return \Config The object instance
     */
    public static function getInstance()
    {
        if (static::$objInstance === null) {
            static::$objInstance = new static();
        }

        return static::$objInstance;
    }

    /**
     * Return a configuration value
     *
     * @param string $strKey The short key (e.g. "displayErrors")
     *
     * @return mixed|null The configuration value
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function get($strKey)
    {
        if (isset($GLOBALS['TL_CONFIG'][$strKey])) {
            return $GLOBALS['TL_CONFIG'][$strKey];
        }

        return null;
    }
}
