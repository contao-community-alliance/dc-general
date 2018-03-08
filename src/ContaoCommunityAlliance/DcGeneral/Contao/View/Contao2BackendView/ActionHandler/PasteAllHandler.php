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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractHandler;

/**
 * Action handler for paste all action.
 *
 * @return void
 */
class PasteAllHandler extends AbstractHandler
{
    /**
     * The copied model is available by paste mode copy.
     *
     * @var ModelIdInterface
     */
    protected $copiedModel;

    /**
     * The original model is available by paste mode copy.
     *
     * @var ModelIdInterface
     */
    protected $originalModel;

    /**
     * Handle the action.
     *
     * @return void
     */
    public function process()
    {
        $event = $this->getEvent();
        if ($event->getAction()->getName() !== 'paste'
            || $this->getEnvironment()->getInputProvider()->getParameter('pasteAll')
        ) {
            return;
        }

        $collection = $this->getCollection();

        // If one item in the clipboard we don´t paste all here.
        if (\count($collection) < 1) {
            return;
        }

        $this->getEnvironment()->getInputProvider()->setParameter('pasteAll', true);

        $this->addDispatchDuplicateModel();

        foreach ($collection as $collectionItem) {
            $this->setParameterForPaste($collectionItem);

            $this->callAction($event->getAction()->getName());

            $clipboardItem = $collectionItem['item'];
            $this->getEnvironment()->getClipboard()->removeById($clipboardItem->getModelId());
        }
        $this->getEnvironment()->getClipboard()->saveTo($this->getEnvironment());

        ViewHelpers::redirectHome($this->getEnvironment());
    }

    /**
     * Get the items from the clipboard.
     *
     * @return array
     */
    protected function getClipboardItems()
    {
        $basicDefinition = $this->getEnvironment()->getDataDefinition()->getBasicDefinition();

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        if ($basicDefinition->getParentDataProvider()) {
            $filter->andParentIsFromProvider($basicDefinition->getParentDataProvider());
        } else {
            $filter->andHasNoParent();
        }

        return $this->getEnvironment()->getClipboard()->fetch($filter);
    }

    /**
     * Get the collection.
     *
     * @return array
     */
    protected function getCollection()
    {
        $dataDefinition = $this->getEnvironment()->getDataDefinition();
        $relationShip   = $dataDefinition->getModelRelationshipDefinition();
        $childCondition = $relationShip->getChildCondition($dataDefinition->getName(), $dataDefinition->getName());

        if (!$childCondition) {
            return $this->getFlatCollection();
        }

        return $this->getHierarchyCollection($this->getClipboardItems());
    }

    /**
     * Get the flat collection.
     *
     * @return array
     */
    protected function getFlatCollection()
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();

        $clipboardItems = $this->getClipboardItems();

        $previousItem = null;

        $collection = [];
        foreach ($clipboardItems as $clipboardItem) {
            if ($clipboardItem->getAction() === 'create') {
                continue;
            }

            $modelId = $clipboardItem->getModelId();

            $pasteAfter =
                $previousItem ? $previousItem->getModelId()->getSerialized() : $inputProvider->getParameter('after');
            $item       = [
                'item'       => $clipboardItem,
                'pasteAfter' => $pasteAfter,
                'pasteMode'  => 'after'
            ];

            $collection[$modelId->getSerialized()] = $item;

            $previousItem = $clipboardItem;
        }

        return $collection;
    }

    /**
     * Get hierarchy collection.
     *
     * @param array $clipboardItems The clipboard items.
     *
     * @return array
     */
    protected function getHierarchyCollection(array $clipboardItems)
    {
        $dataProvider   = $this->getEnvironment()->getDataProvider();
        $inputProvider  = $this->getEnvironment()->getInputProvider();
        $dataDefinition = $this->getEnvironment()->getDataDefinition();
        $relationShip   = $dataDefinition->getModelRelationshipDefinition();
        $childCondition = $relationShip->getChildCondition($dataDefinition->getName(), $dataDefinition->getName());

        $collection = [];

        $originalPasteMode = $inputProvider->hasParameter('after') ? 'after' : 'into';

        $previousItem = null;
        foreach ($clipboardItems as $clipboardItem) {
            $modelId = $clipboardItem->getModelId();
            if (!$modelId
                || \array_key_exists($modelId->getSerialized(), $collection)
            ) {
                continue;
            }

            $pasteMode  = $previousItem ? 'after' : $originalPasteMode;
            $pasteAfter =
                $previousItem ? $previousItem->getModelId()->getSerialized() : $inputProvider->getParameter($pasteMode);
            $item       = [
                'item'       => $clipboardItem,
                'pasteAfter' => $pasteAfter,
                'pasteMode'  => $pasteMode
            ];

            $collection[$modelId->getSerialized()] = $item;

            $previousItem = $clipboardItem;

            $model =
                $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

            $itemCollection =
                $dataProvider->fetchAll($dataProvider->getEmptyConfig()->setFilter($childCondition->getFilter($model)));
            if ($itemCollection) {
                $collection = $this->setSubItemsToCollection(
                    $clipboardItem,
                    $this->getSubClipboardItems($clipboardItems, $itemCollection),
                    $collection
                );
            }
        }

        return $collection;
    }

    /**
     * Get the sub items from the clipboard.
     *
     * @param array               $clipboardItems The clipboard items.
     * @param CollectionInterface $collection     The collection.
     *
     * @return array
     */
    protected function getSubClipboardItems(array $clipboardItems, CollectionInterface $collection)
    {
        $subClipboardItems = [];

        $modelIds = $collection->getModelIds();
        foreach ($clipboardItems as $clipboardItem) {
            if (!\in_array($clipboardItem->getModelId()->getId(), $modelIds)) {
                continue;
            }

            $subClipboardItems[] = $clipboardItem;
        }

        return $subClipboardItems;
    }

    /**
     * Set the sub items to the collection.
     *
     * @param ItemInterface $previousItem      The previous item.
     * @param array         $subClipboardItems The sub clipboard items.
     * @param array         $collection        The collection.
     *
     * @return array
     */
    protected function setSubItemsToCollection(ItemInterface $previousItem, array $subClipboardItems, array $collection)
    {
        if (empty($subClipboardItems)) {
            return $collection;
        }

        $dataProvider   = $this->getEnvironment()->getDataProvider();
        $dataDefinition = $this->getEnvironment()->getDataDefinition();
        $relationShip   = $dataDefinition->getModelRelationshipDefinition();
        $childCondition = $relationShip->getChildCondition($dataDefinition->getName(), $dataDefinition->getName());

        $previousModelId = $previousItem->getModelId();

        $intoItem = null;
        foreach ($subClipboardItems as $subClipboardItem) {
            $modelId = $subClipboardItem->getModelId();

            $pasteMode  = $intoItem ? 'after' : 'into';
            $pasteAfter =
                $intoItem ? $intoItem->getModelId()->getSerialized() : $previousModelId->getSerialized();
            $item       = [
                'item'       => $subClipboardItem,
                'pasteAfter' => $pasteAfter,
                'pasteMode'  => $pasteMode
            ];

            $intoItem = $subClipboardItem;

            $collection[$modelId->getSerialized()] = $item;

            $model          =
                $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
            $itemCollection =
                $dataProvider->fetchAll($dataProvider->getEmptyConfig()->setFilter($childCondition->getFilter($model)));

            if ($itemCollection) {
                $collection = $this->setSubItemsToCollection(
                    $subClipboardItem,
                    $this->getSubClipboardItems($this->getClipboardItems(), $itemCollection),
                    $collection
                );
            }
        }

        return $collection;
    }

    /**
     * Add the event to the listeners for post duplicate model event.
     *
     * @return void
     */
    protected function addDispatchDuplicateModel()
    {
        $this->getEnvironment()->getEventDispatcher()->addListener(
            PostDuplicateModelEvent::NAME,
            function (PostDuplicateModelEvent $event) {
                $this->copiedModel   = $event->getModel();
                $this->originalModel = $event->getSourceModel();
            }
        );
    }

    /**
     * Set the parameter for paste.
     *
     * @param array $collectionItem The collection item.
     *
     * @return void
     */
    protected function setParameterForPaste(array $collectionItem)
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();
        $clipboardItem = $collectionItem['item'];

        $inputProvider->unsetParameter('after');
        $inputProvider->unsetParameter('into');
        $inputProvider->unsetParameter('source');
        $inputProvider->setParameter('source', $clipboardItem->getModelId()->getSerialized());

        if (!$this->originalModel) {
            $inputProvider->setParameter($collectionItem['pasteMode'], $collectionItem['pasteAfter']);

            return;
        }

        $pasteAfterId = ModelId::fromSerialized($collectionItem['pasteAfter']);
        if ($pasteAfterId->getId() !== $this->originalModel->getID()) {
            $inputProvider->setParameter($collectionItem['pasteMode'], $collectionItem['pasteAfter']);

            return;
        }

        $copiedModelId = ModelId::fromModel($this->copiedModel);

        $inputProvider->setParameter($collectionItem['pasteMode'], $copiedModelId->getSerialized());
    }
}
