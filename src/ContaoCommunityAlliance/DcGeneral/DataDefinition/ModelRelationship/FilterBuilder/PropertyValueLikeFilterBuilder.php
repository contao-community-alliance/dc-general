<?php
/**
 * PHP version 5
 *
 * @package    DcGeneral
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

/**
 * Handy helper class to generate and manipulate AND filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship\FilterBuilder
 */
class PropertyValueLikeFilterBuilder extends BaseComparingFilterBuilder
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
        $this->operation = 'LIKE';
        parent::__construct($property, $value, $isRemote, $isRemoteProp);
    }
}
