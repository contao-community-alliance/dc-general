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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;

/**
 * Default implementation of a model relationship definition.
 *
 * @package DcGeneral\DataDefinition\Definition
 */
class DefaultModelRelationshipDefinition implements ModelRelationshipDefinitionInterface
{
    /**
     * The root condition relationship.
     *
     * @var RootConditionInterface
     */
    protected $rootCondition;

    /**
     * A collection of parent child conditions.
     *
     * @var ParentChildConditionInterface[]
     */
    protected $childConditions = array();

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
        $hash = spl_object_hash($condition);

        $this->childConditions[$hash] = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildCondition($srcProvider, $dstProvider)
    {
        foreach ($this->getChildConditions($srcProvider) as $condition)
        {
            if ($condition->getDestinationName() == $dstProvider)
            {
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

        if (!$this->childConditions)
        {
            return array();
        }

        $arrReturn = array();
        foreach ($this->childConditions as $condition)
        {
            if ($condition->getSourceName() != $srcProvider)
            {
                continue;
            }

            $arrReturn[] = $condition;
        }

        return $arrReturn;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        if ($this->rootCondition !== null)
        {
            $this->rootCondition = clone $this->rootCondition;
        }

        $conditions = array();
        foreach ($this->childConditions as $condition)
        {
            $bobaFett = clone $condition;

            $conditions[] = $bobaFett;
        }
        $this->childConditions = $conditions;
    }
}
