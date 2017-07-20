<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;

/**
 * This interface describes the relationships between data providers.
 *
 * It holds a root condition for the root data provider and many parent child relationships.
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
