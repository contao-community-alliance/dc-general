<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

/**
 * Handy helper class to generate and manipulate less than filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
 */
class PropertyLessThanFilterBuilder extends BaseComparingFilterBuilder
{
    /**
     * Create a new instance.
     *
     * @param string $property     The property name to be compared.
     *
     * @param mixed  $value        The value to be compared against.
     *
     * @param bool   $isRemote     Flag determining if the passed value is a remote property name (only valid if filter
     *                             is for parent child relationship and not for root elements).
     *
     * @param bool   $isRemoteProp Flag determining if the passed value is a property or literal value (only valid when
     *                             $isRemote is true).
     */
    public function __construct($property, $value, $isRemote = false, $isRemoteProp = true)
    {
        $this->operation = '>';
        parent::__construct($property, $value, $isRemote, $isRemoteProp);
    }
}
