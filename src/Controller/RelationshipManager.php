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

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class handles all parent child relationship management.
 */
class RelationshipManager
{
    /**
     * The model relationships.
     *
     * @var ModelRelationshipDefinitionInterface
     */
    private $relationships;

    /**
     * The definition mode.
     *
     * @var int
     *
     * @see \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface
     */
    private $mode;

    /**
     * Create a new instance.
     *
     * @param ModelRelationshipDefinitionInterface $relationships The relationship list.
     * @param int                                  $mode          The mode.
     */
    public function __construct(ModelRelationshipDefinitionInterface $relationships, $mode)
    {
        $this->relationships = $relationships;
        $this->mode          = $mode;
    }

    /**
     * Check if the given model is a root model for the current data definition.
     *
     * @param ModelInterface $model The model to check.
     *
     * @return bool
     *
     * @throws DcGeneralRuntimeException When no root condition is defined.
     */
    public function isRoot(ModelInterface $model)
    {
        if ($this->mode !== BasicDefinitionInterface::MODE_HIERARCHICAL) {
            return false;
        }

        $condition = $this->relationships->getRootCondition();
        if (!$condition instanceof RootConditionInterface) {
            throw new DcGeneralRuntimeException('No root condition defined');
        }

        return $condition->matches($model);
    }

    /**
     * Apply the root condition of the current data definition to the given model.
     *
     * @param ModelInterface $model The model to be used as root.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When no root condition is defined.
     */
    public function setRoot(ModelInterface $model)
    {
        if ($this->mode !== BasicDefinitionInterface::MODE_HIERARCHICAL) {
            return;
        }

        $condition = $this->relationships->getRootCondition();
        if (!$condition instanceof RootConditionInterface) {
            throw new DcGeneralRuntimeException('No root condition defined');
        }

        $condition->applyTo($model);
    }

    /**
     * Sets the parent for all models.
     *
     * @param CollectionInterface $models The collection of models to mark as root.
     *
     * @return void
     */
    public function setAllRoot(CollectionInterface $models)
    {
        foreach ($models as $model) {
            $this->setRoot($model);
        }
    }

    /**
     * Set a model as the parent of another model.
     *
     * @param ModelInterface $childModel  The model to become the child.
     *
     * @param ModelInterface $parentModel The model to use as parent.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When no condition is defined.
     */
    public function setParent(ModelInterface $childModel, ModelInterface $parentModel)
    {
        $condition = $this->relationships->getChildCondition(
            $parentModel->getProviderName(),
            $childModel->getProviderName()
        );
        if (!$condition instanceof ParentChildConditionInterface) {
            throw new DcGeneralRuntimeException(
                'No condition defined from ' . $parentModel->getProviderName() . ' to ' . $childModel->getProviderName()
            );
        }

        $condition->applyTo($parentModel, $childModel);
    }

    /**
     * Sets the parent for all models.
     *
     * @param CollectionInterface $models      The collection of models to apply the parent to.
     *
     * @param ModelInterface      $parentModel The new parent model.
     *
     * @return void
     */
    public function setParentForAll(CollectionInterface $models, ModelInterface $parentModel)
    {
        foreach ($models as $model) {
            $this->setParent($model, $parentModel);
        }
    }

    /**
     * Sets all parent condition fields in the destination to the values from the source model.
     *
     * Useful when moving an element after another in a different parent.
     *
     * @param ModelInterface $receivingModel The model that shall get updated.
     *
     * @param ModelInterface $sourceModel    The model that the values shall get retrieved from.
     *
     * @param string         $parentTable    The name of the parent table for the models.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When no condition is defined.
     */
    public function setSameParent(ModelInterface $receivingModel, ModelInterface $sourceModel, $parentTable)
    {
        $condition = $this->relationships->getChildCondition($parentTable, $receivingModel->getProviderName());
        if (!$condition instanceof ParentChildConditionInterface) {
            throw new DcGeneralRuntimeException(
                'No condition defined from ' . $parentTable . ' to ' . $receivingModel->getProviderName()
            );
        }

        $condition->copyFrom($sourceModel, $receivingModel);
    }

    /**
     * Sets the same parent for all models.
     *
     * @param CollectionInterface $models      The collection of models to apply the parent to.
     *
     * @param ModelInterface      $sourceModel The model that the values shall get retrieved from.
     *
     * @param string              $parentTable The name of the parent table for the models.
     *
     * @return void
     */
    public function setSameParentForAll(CollectionInterface $models, ModelInterface $sourceModel, $parentTable)
    {
        foreach ($models as $model) {
            $this->setSameParent($model, $sourceModel, $parentTable);
        }
    }
}
