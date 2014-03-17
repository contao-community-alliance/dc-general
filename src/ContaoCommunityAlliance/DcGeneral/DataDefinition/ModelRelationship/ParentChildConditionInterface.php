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
 * This interface holds the information how a parent model relates to a child model.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship
 */
interface ParentChildConditionInterface
{
	/**
	 * Set the condition as filter.
	 *
	 * @param array $value The filter rules describing the relationship.
	 *
	 * @return ParentChildConditionInterface
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
	 * @param array $value The values to be applied to a model when it shall get set as child of another one.
	 *
	 * @return ParentChildConditionInterface
	 */
	public function setSetters($value);

	/**
	 * Get the condition setters.
	 *
	 * @return array
	 */
	public function getSetters();

	/**
	 * Set the inverse filter for the condition.
	 *
	 * @param array $value The filter rules to use when inverting the condition to look up the parent.
	 *
	 * @return ParentChildConditionInterface
	 */
	public function setInverseFilterArray($value);

	/**
	 * Get the inverse filter for the condition.
	 *
	 * @return array
	 */
	public function getInverseFilterArray();

	/**
	 * Get the condition as filter related to the given model.
	 *
	 * @param ModelInterface $objParent The model that shall get used as parent.
	 *
	 * @return array
	 */
	public function getFilter($objParent);

	/**
	 * Set the name of the source provider.
	 *
	 * @param string $value The name of the provider.
	 *
	 * @return ParentChildConditionInterface
	 */
	public function setSourceName($value);

	/**
	 * Return the name of the source provider (parent).
	 *
	 * @return string
	 */
	public function getSourceName();

	/**
	 * Set the name of the destination provider (child).
	 *
	 * @param string $value The name of the provider.
	 *
	 * @return ParentChildConditionInterface
	 */
	public function setDestinationName($value);

	/**
	 * Return the name of the destination provider.
	 *
	 * @return string
	 */
	public function getDestinationName();

	/**
	 * Apply a condition to a child.
	 *
	 * @param ModelInterface $objParent The parent object.
	 *
	 * @param ModelInterface $objChild  The object on which the condition shall be enforced on.
	 *
	 * @return void
	 */
	public function applyTo($objParent, $objChild);

	/**
	 * Apply a condition to a child by copying it from another child.
	 *
	 * @param ModelInterface $sourceModel      The sibling model.
	 *
	 * @param ModelInterface $destinationModel The model on which the condition shall be enforced on.
	 *
	 * @return void
	 */
	public function copyFrom($sourceModel, $destinationModel);

	/**
	 * Get the inverted condition as filter.
	 *
	 * This allows to look up the parent of a child model.
	 *
	 * @param ModelInterface $objChild The model that shall get used as child and for which the parent filter shall get
	 *                                 retrieved.
	 *
	 * @return array|null
	 */
	public function getInverseFilterFor($objChild);

	/**
	 * Test if the given parent is indeed a parent of the given child object for this condition.
	 *
	 * @param ModelInterface $objParent The parent model.
	 *
	 * @param ModelInterface $objChild  The child model.
	 *
	 * @return bool
	 */
	public function matches($objParent, $objChild);
}
