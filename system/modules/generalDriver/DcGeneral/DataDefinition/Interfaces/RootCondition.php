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

interface RootCondition extends Condition
{
	/**
	 * Get the condition as filter.
	 *
	 * @return array
	 */
	public function getFilter();

	/**
	 * Apply a condition to a model.
	 *
	 * @param \DcGeneral\Data\Interfaces\Model $objModel
	 *
	 * @return void
	 */
	public function applyTo($objModel);

	/**
	 * Test if the given model is indeed a root object for this condition.
	 *
	 * @param \DcGeneral\Data\Interfaces\Model $objModel
	 *
	 * @return bool
	 */
	public function matches($objModel);
}
