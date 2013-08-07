<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\Interfaces;

use DcGeneral\Data\Interfaces\Model;

interface ParentChildCondition extends Condition
{
	/**
	 * Get the condition as filter.
	 *
	 * @param Model $objParent The model that shall get used as parent.
	 *
	 * @return array
	 */
	public function getFilter($objParent);

	/**
	 * Apply a condition to a child.
	 *
	 * @param \DcGeneral\Data\Interfaces\Model $objParent The parent object.
	 *
	 * @param \DcGeneral\Data\Interfaces\Model $objChild The object on which the condition shall be enforced on.
	 *
	 * @return void
	 */
	public function applyTo($objParent, $objChild);

	/**
	 * Test if the given parent is indeed a parent of the given child object for this condition.
	 *
	 * @param \DcGeneral\Data\Interfaces\Model $objParent
	 *
	 * @param \DcGeneral\Data\Interfaces\Model $objChild
	 *
	 * @return bool
	 */
	public function matches($objParent, $objChild);

	/**
	 * Return the name of the source provider.
	 *
	 * @return string
	 */
	public function getSourceName();

	/**
	 * Return the name of the destination provider.
	 *
	 * @return string
	 */
	public function getDestinationName();
}
