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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\FilterInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This interface describes a controller.
 */
interface ControllerInterface
{
    /**
     * Set the environment.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return ControllerInterface
     */
    public function setEnvironment(EnvironmentInterface $environment);

    /**
     * Retrieve the attached environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment();

    /**
     * Handle a action within this environment.
     *
     * @param Action $action The action to be executed.
     *
     * @return string
     */
    public function handle(Action $action);

    /**
     * Search the parent of the passed model.
     *
     * @param ModelInterface      $model  The model to search the parent for.
     *
     * @param CollectionInterface $models The collection to search in.
     *
     * @return ModelInterface
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::searchParentOfIn().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::searchParentOfIn().
     */
    public function searchParentOfIn(ModelInterface $model, CollectionInterface $models);

    /**
     * Search the parent model for the given model.
     *
     * @param ModelInterface $model The model for which the parent shall be retrieved.
     *
     * @return ModelInterface|null
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::searchParentOf().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::searchParentOf().
     */
    public function searchParentOf(ModelInterface $model);

    /**
     * Scan for children of a given model.
     *
     * This method is ready for mixed hierarchy and will return all children and grandchildren for the given table
     * (or originating table of the model, if no provider name has been given) for all levels and parent child
     * conditions.
     *
     * @param ModelInterface $objModel        The model to assemble children from.
     *
     * @param string         $strDataProvider The name of the data provider to fetch children from.
     *
     * @return array
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::collectChildrenOf().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::collectChildrenOf().
     */
    public function assembleAllChildrenFrom($objModel, $strDataProvider = '');

    /**
     * Update the current model from a post request. Additionally, trigger meta palettes, if installed.
     *
     * @param ModelInterface            $model          The model to update.
     *
     * @param PropertyValueBagInterface $propertyValues The value bag to retrieve the values from.
     *
     * @return ControllerInterface
     */
    public function updateModelFromPropertyBag($model, $propertyValues);

    /**
     * Return all supported languages from the default data data provider.
     *
     * @param mixed $mixID The id of a model for which the languages shall be retrieved.
     *
     * @return array
     */
    public function getSupportedLanguages($mixID);

    /**
     * Create a cloned version of the passed model.
     *
     * The cloning involves clearing of unknown properties, resetting the fallback and clearing properties defined as
     * "doNotCopy".
     *
     * @param ModelInterface $model The model to clone.
     *
     * @return ModelInterface
     */
    public function createClonedModel($model);

    /**
     * Fetch a certain model from its provider.
     *
     * @param string|ModelIdInterface $modelId      This is either the id of the model or a serialized id.
     *
     * @param string|null             $providerName The name of the provider, if this is empty, the id will be
     *                                              deserialized and the provider name will get extracted from there.
     *
     * @return ModelInterface
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::getModel().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector::getModel().
     */
    public function fetchModelFromProvider($modelId, $providerName = null);

    /**
     * Create an empty model using the default values from the definition.
     *
     * @return ModelInterface
     */
    public function createEmptyModelWithDefaults();

    /**
     * Retrieve a model from the clipboard item.
     *
     * @param ItemInterface $item The clipboard item.
     *
     * @return ModelInterface|null
     */
    public function getModelFromClipboardItem(ItemInterface $item);

    /**
     * Retrieve models from the clipboard items.
     *
     * @param array|ItemInterface[] $items The clipboard items.
     *
     * @return CollectionInterface|ModelInterface[]
     */
    public function getModelsFromClipboardItems(array $items);

    /**
     * Retrieve models from the clipboard.
     *
     * This will only return models, that are compatible with the current environment.
     *
     * @param ModelIdInterface $parentModelId The optional parent id. If not given, the models must not have a parent.
     *
     * @return CollectionInterface|\ContaoCommunityAlliance\DcGeneral\Data\ModelInterface[]
     */
    public function getModelsFromClipboard(ModelIdInterface $parentModelId = null);

    /**
     * Evaluate clipboard items, then return the corresponding models.
     *
     * @param ModelIdInterface $source        The source model id.
     * @param ModelIdInterface $after         The previous model id.
     * @param ModelIdInterface $into          The hierarchical parent model id.
     * @param ModelIdInterface $parentModelId The parent model id.
     * @param FilterInterface  $filter        Clipboard filter.
     * @param array            $items         Write-back evaluated clipboard items.
     *
     * @return CollectionInterface|ModelInterface[]
     */
    public function applyClipboardActions(
        ModelIdInterface $source = null,
        ModelIdInterface $after = null,
        ModelIdInterface $into = null,
        ModelIdInterface $parentModelId = null,
        FilterInterface $filter = null,
        array &$items = array()
    );

    /**
     * Paste the content of the clipboard onto the top.
     *
     * @param CollectionInterface $models   The models to be inserted.
     *
     * @param string              $sortedBy The name of the sorting property.
     *
     * @param ModelIdInterface    $parentId The parent model ID.
     *
     * @return void
     */
    public function pasteTop(CollectionInterface $models, $sortedBy, ModelIdInterface $parentId = null);

    /**
     * Paste the content of the clipboard after the given model.
     *
     * @param ModelInterface      $previousModel The model after which to paste.
     *
     * @param CollectionInterface $models        The models to be inserted.
     *
     * @param string              $sortedBy      The name of the sorting property.
     *
     * @return void
     */
    public function pasteAfter(ModelInterface $previousModel, CollectionInterface $models, $sortedBy);

    /**
     * Paste the content of the clipboard into the given model.
     *
     * @param ModelInterface      $parentModel The model to become the parent model of the clipboard content.
     *
     * @param CollectionInterface $models      The models to be inserted.
     *
     * @param string              $sortedBy    The name of the sorting property.
     *
     * @return void
     */
    public function pasteInto(ModelInterface $parentModel, CollectionInterface $models, $sortedBy);

    /**
     * Check if the given model is a root model for the current data definition.
     *
     * @param ModelInterface $model The model to check.
     *
     * @return bool
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::isRoot().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::isRoot()
     */
    public function isRootModel(ModelInterface $model);

    /**
     * Apply the root condition of the current data definition to the given model.
     *
     * @param ModelInterface $model The model to be used as root.
     *
     * @return ControllerInterface
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setRoot().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setRoot()
     */
    public function setRootModel(ModelInterface $model);

    /**
     * Set a model as the parent of another model.
     *
     * @param ModelInterface $childModel  The model to become the child.
     *
     * @param ModelInterface $parentModel The model to use as parent.
     *
     * @return ControllerInterface
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setParent().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setParent()
     */
    public function setParent(ModelInterface $childModel, ModelInterface $parentModel);

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
     * @return ControllerInterface
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setSameParent().
     *
     * @see \ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager::setSameParent()
     */
    public function setSameParent(ModelInterface $receivingModel, ModelInterface $sourceModel, $parentTable);
}
