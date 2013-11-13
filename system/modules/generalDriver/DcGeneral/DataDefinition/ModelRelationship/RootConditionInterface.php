<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\ModelRelationship;

use DcGeneral\Data\ModelInterface;

interface RootConditionInterface
{
	/**
	 * Set the condition as filter.
	 *
	 * @param array $value
	 *
	 * @return RootConditionInterface
	 */
	public function setFilterArray($value);

	/**
	 * Get the condition as filter.
	 *
	 * @return array
	 */
	public function getFilterArray();

	/**
	 * Set the condition setters.
	 *
	 * @param array $value
	 *
	 * @return RootConditionInterface
	 */
	public function setSetters($value);

	/**
	 * Get the condition setters.
	 *
	 * @return array
	 */
	public function getSetters();

	/**
	 * Set the name of the source provider.
	 *
	 * @param string $value
	 *
	 * @return RootConditionInterface
	 */
	public function setSourceName($value);

	/**
	 * Return the name of the source provider.
	 *
	 * @return string
	 */
	public function getSourceName();

	/**
	 * Apply the root condition to a model.
	 *
	 * @param ModelInterface $objModel
	 *
	 * @return RootConditionInterface
	 */
	public function applyTo($objModel);

	/**
	 * Test if the given model is indeed a root object according to this condition.
	 *
	 * @param ModelInterface $objModel
	 *
	 * @return bool
	 */
	public function matches($objModel);
}
