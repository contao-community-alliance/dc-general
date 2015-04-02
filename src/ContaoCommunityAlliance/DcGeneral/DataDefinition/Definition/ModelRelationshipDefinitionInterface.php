<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;

/**
 * This interface describes the relationships between data providers.
 *
 * It holds a root condition for the root data provider and many parent child relationships.
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
     * @param RootConditionInterface $condition The root condition.
     *
     * @return ModelRelationshipDefinitionInterface
     */
    public function setRootCondition($condition);

    /**
     * Retrieve the root condition for the current table.
     *
     * @return RootConditionInterface
     */
    public function getRootCondition();

    /**
     * Add a parent child condition.
     *
     * @param ParentChildConditionInterface $condition The parent child condition.
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
     * @return ParentChildConditionInterface
     */
    public function getChildCondition($srcProvider, $dstProvider);

    /**
     * Retrieve the parent child conditions for the current table.
     *
     * @param string $srcProvider The parenting table for which child conditions shall be assembled for (optional).
     *
     * @return ParentChildConditionInterface[]
     */
    public function getChildConditions($srcProvider = '');
}
