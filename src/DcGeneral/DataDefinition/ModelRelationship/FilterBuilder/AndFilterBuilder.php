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

namespace DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

use DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Handy helper class to generate and manipulate AND filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship\FilterBuilder
 */
class AndFilterBuilder
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

		$this->operation = 'AND';
	}

	/**
	 * Absorb the given filter builder or filter builder collection.
	 *
	 * @param FilterBuilder|FilterBuilderWithChildren $filters The input.
	 *
	 * @return FilterBuilderWithChildren
	 */
	public function append($filters)
	{
		if ($filters instanceof FilterBuilder)
		{
			$filters = $filters->getFilter();
		}

		if ($filters instanceof AndFilterBuilder)
		{
			foreach ((clone $filters) as $filter)
			{
				$this->add(clone $filter);
			}

			return $this;
		}

		$this->andEncapsulate(clone $filters);

		return $this;
	}
}
