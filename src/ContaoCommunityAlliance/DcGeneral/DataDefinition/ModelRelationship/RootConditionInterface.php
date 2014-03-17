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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;

/**
 * This interface holds the information about the characteristics of a root model.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship
 */
interface RootConditionInterface
{
	/**
	 * Set the condition as filter.
	 *
	 * @param array $value The filter rules to be used for finding root models.
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
	 * @param array $value The values to be used when making a model a root model.
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
	 * @param string $value The data provider name.
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
	 * @param ModelInterface $objModel The model that shall become a root model.
	 *
	 * @return RootConditionInterface
	 */
	public function applyTo($objModel);

	/**
	 * Test if the given model is indeed a root object according to this condition.
	 *
	 * @param ModelInterface $objModel The model to be tested.
	 *
	 * @return bool
	 */
	public function matches($objModel);
}
