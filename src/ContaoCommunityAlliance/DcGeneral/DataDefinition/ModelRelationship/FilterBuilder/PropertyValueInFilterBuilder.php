<?php
/**
 * PHP version 5
 * @package    DcGeneral
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
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
class PropertyValueInFilterBuilder
	extends BaseComparingFilterBuilder
{

	/**
	 * Create a new instance.
	 *
	 * @param string $property The property name to be compared.
	 *
	 * @param mixed  $value    The value to be compared against.
	 */
	public function __construct($property, $value)
	{
		$this->operation = 'IN';
		parent::__construct($property, $value);
	}
}
