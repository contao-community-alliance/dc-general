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

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Handy helper class to generate and manipulate OR filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship\FilterBuilder
 */
class OrFilterBuilder
	extends FilterBuilderWithChildren
{
	/**
	 * Create a new instance.
	 *
	 * @param array $children The initial children to absorb.
	 *
	 * @throws DcGeneralInvalidArgumentException When invalid children have been passed.
	 */
	public function __construct($children = array())
	{
		parent::__construct($children);

		$this->operation = 'OR';
	}
}
