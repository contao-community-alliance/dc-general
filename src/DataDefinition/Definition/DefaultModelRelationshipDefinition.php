<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;

/**
 * Default implementation of a model relationship definition.
 */
class DefaultModelRelationshipDefinition implements ModelRelationshipDefinitionInterface
{
    /**
     * The root condition relationship.
     *
     * @var RootConditionInterface|null
     */
    protected $rootCondition = null;

    /**
     * A collection of parent child conditions.
     *
     * @var ParentChildConditionInterface[]
     */
    protected $childConditions = [];

    /**
     * {@inheritdoc}
     */
    public function setRootCondition($condition)
    {
        $this->rootCondition = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootCondition()
    {
        return $this->rootCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function addChildCondition($condition)
    {
        $this->childConditions[\spl_object_hash($condition)] = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildCondition($srcProvider, $dstProvider)
    {
        foreach ($this->getChildConditions($srcProvider) as $condition) {
            if ($dstProvider === $condition->getDestinationName()) {
                return $condition;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildConditions($srcProvider = '')
    {
        if (!$this->childConditions) {
            return [];
        }

        $return = [];
        foreach ($this->childConditions as $condition) {
            if (!empty($srcProvider) && ($srcProvider !== $condition->getSourceName())) {
                continue;
            }

            $return[] = $condition;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        if (null !== $this->rootCondition) {
            $this->rootCondition = clone $this->rootCondition;
        }

        $conditions = [];
        foreach ($this->childConditions as $condition) {
            $bobaFett = clone $condition;

            $conditions[\spl_object_hash($bobaFett)] = $bobaFett;
        }
        $this->childConditions = $conditions;
    }
}
