<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
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
        foreach ($this->getChildConditions($srcProvider) as $condition) {
            if ($condition->getDestinationName() == $dstProvider) {
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
            return array();
        }

        $arrReturn = array();
        foreach ($this->childConditions as $condition) {
            if (!empty($srcProvider) && ($condition->getSourceName() != $srcProvider)) {
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
        if ($this->rootCondition !== null) {
            $this->rootCondition = clone $this->rootCondition;
        }

        $conditions = array();
        foreach ($this->childConditions as $condition) {
            $bobaFett = clone $condition;

            $conditions[spl_object_hash($bobaFett)] = $bobaFett;
        }
        $this->childConditions = $conditions;
    }
}
