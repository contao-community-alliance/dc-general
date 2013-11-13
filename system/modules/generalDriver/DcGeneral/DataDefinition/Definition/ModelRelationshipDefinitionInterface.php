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

namespace DcGeneral\DataDefinition\Definition;

/**
 * Interface RelationshipDefinitionInterface
 *
 * @package DcGeneral\DataDefinition\Definition
 */
interface ModelRelationshipDefinitionInterface extends DefinitionInterface
{
	/**
	 * The name of the definition.
	 */
	const NAME = 'model-relationships';

	/**
	 * Set the root condition for the current table.
	 *
	 * @param \DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface $condition
	 *
	 * @return ModelRelationshipDefinitionInterface
	 */
	public function setRootCondition($condition);

	/**
	 * Retrieve the root condition for the current table.
	 *
	 * @return \DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface
	 */
	public function getRootCondition();

	/**
	 * @param \DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface $condition
	 *
	 * @return ModelRelationshipDefinitionInterface
	 */
	public function addChildCondition($condition);

	/**
	 * Retrieve the parent child condition for the current table.
	 *
	 * @param string $srcProvider The parenting table.
	 *
	 * @param string $dstProvider The child table.
	 *
	 * @return \DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface
	 */
	public function getChildCondition($srcProvider, $dstProvider);

	/**
	 * Retrieve the parent child conditions for the current table.
	 *
	 * @param string $srcProvider The parenting table for which child conditions shall be assembled for (optional).
	 *
	 * @return \DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface[]
	 */
	public function getChildConditions($srcProvider = '');
}
