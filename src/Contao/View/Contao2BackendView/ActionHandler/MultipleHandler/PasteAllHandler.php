<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\MultipleHandler;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Action handler for paste all action.
 *
 * @psalm-type TPasteItem = array{item: ItemInterface, pasteAfter: mixed, pasteMode: string}
 * @psalm-type TPasteCollection = array<string, TPasteItem>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 */
class PasteAllHandler
{
    use RequestScopeDeterminatorAwareTrait;
    use CallActionTrait;

    /**
     * The copied model is available by paste mode copy.
     *
     * @var ModelInterface|null
     */
    protected ?ModelInterface $copiedModel = null;

    /**
     * The original model is available by paste mode copy.
     *
     * @var ModelInterface|null
     */
    protected ?ModelInterface $originalModel = null;


    /**
     * PasteAllHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * {@inheritDoc}
     */
    public function handleEvent(ActionEvent $event): void
    {
        if (
            !$this->getScopeDeterminator()->currentScopeIsBackend()
            || ('pasteAll' !== $event->getAction()->getName())
        ) {
            return;
        }

        $this->process($event->getEnvironment());
    }

    /**
     * Process the paste all handler.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    private function process(EnvironmentInterface $environment)
    {
        $collection = $this->getCollection($environment);

        // If one item in the clipboard we don´t paste all here.
        if (\count($collection) < 1) {
            return;
        }

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $inputProvider->setParameter('pasteAll', true);

        $this->addDispatchDuplicateModel($environment);

        foreach ($collection as $collectionItem) {
            $this->setParameterForPaste($collectionItem, $environment);

            $this->callAction($environment, 'paste');

            $clipboardItem = $collectionItem['item'];
            $modelId       = $clipboardItem->getModelId();
            if (null !== $modelId) {
                $clipboard->removeById($modelId);
            }
        }
        $clipboard->saveTo($environment);

        $inputProvider->unsetParameter('pasteAll');

        ViewHelpers::redirectHome($environment);
    }

    /**
     * Get the items from the clipboard.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return list<ItemInterface>
     */
    protected function getClipboardItems(EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();
        assert($basicDefinition instanceof BasicDefinitionInterface);

        $provider = $basicDefinition->getDataProvider();
        assert(\is_string($provider));

        $parentProvider = $basicDefinition->getParentDataProvider();
        assert(\is_string($parentProvider));

        $filter = new Filter();
        $filter->andModelIsFromProvider($provider);
        if (null !== $basicDefinition->getParentDataProvider()) {
            $filter->andParentIsFromProvider($parentProvider);
        } else {
            $filter->andHasNoParent();
        }

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        return $clipboard->fetch($filter);
    }

    /**
     * Get the collection.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return TPasteCollection
     */
    protected function getCollection(EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $relationShip   = $dataDefinition->getModelRelationshipDefinition();

        if (!$relationShip->getChildCondition($dataDefinition->getName(), $dataDefinition->getName())) {
            return $this->getFlatCollection($environment);
        }

        return $this->getHierarchyCollection($this->getClipboardItems($environment), $environment);
    }

    /**
     * Get the flat collection.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return TPasteCollection
     */
    protected function getFlatCollection(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $previousModelId = null;
        $collection      = [];
        foreach ($this->getClipboardItems($environment) as $clipboardItem) {
            if ('create' === $clipboardItem->getAction()) {
                continue;
            }
            $modelId = $clipboardItem->getModelId();
            if (null === $modelId) {
                continue;
            }
            /** @var mixed $pasteAfter */
            $pasteAfter =
                null !== $previousModelId
                ? $previousModelId->getSerialized()
                : $inputProvider->getParameter('after');

            $collection[$modelId->getSerialized()] = [
                'item'       => $clipboardItem,
                'pasteAfter' => $pasteAfter,
                'pasteMode'  => 'after'
            ];

            $previousModelId = $modelId;
        }

        return $collection;
    }

    /**
     * Get hierarchy collection.
     *
     * @param list<ItemInterface>  $clipboardItems The clipboard items.
     * @param EnvironmentInterface $environment    The environment.
     *
     * @return TPasteCollection
     *
     * @throws DcGeneralInvalidArgumentException Invalid configuration. Child condition must be defined.
     */
    protected function getHierarchyCollection(array $clipboardItems, EnvironmentInterface $environment)
    {
        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        $inputProvider  = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $relationShip   = $dataDefinition->getModelRelationshipDefinition();
        $childCondition = $relationShip->getChildCondition($dataDefinition->getName(), $dataDefinition->getName());
        if (null === $childCondition) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. Child condition must be defined!'
            );
        }

        $collection = [];

        $originalPasteMode = $inputProvider->hasParameter('after') ? 'after' : 'into';

        $previousModelId = null;
        foreach ($clipboardItems as $clipboardItem) {
            $modelId = $clipboardItem->getModelId();
            if (!$modelId || \array_key_exists($modelId->getSerialized(), $collection)) {
                continue;
            }

            $pasteMode  = null !== $previousModelId ? 'after' : $originalPasteMode;
            /** @var mixed $pasteAfter */
            $pasteAfter =
                null !== $previousModelId
                    ? $previousModelId->getSerialized()
                    : $inputProvider->getParameter($pasteMode);

            $collection[$modelId->getSerialized()] = [
                'item'       => $clipboardItem,
                'pasteAfter' => $pasteAfter,
                'pasteMode'  => $pasteMode
            ];

            $previousModelId = $modelId;

            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
            assert($model instanceof ModelInterface);

            $itemCollection =
                $dataProvider->fetchAll($dataProvider->getEmptyConfig()->setFilter($childCondition->getFilter($model)));
            assert($itemCollection instanceof CollectionInterface);

            $collection = $this->setSubItemsToCollection(
                $clipboardItem,
                $this->getSubClipboardItems($clipboardItems, $itemCollection),
                $collection,
                $environment
            );
        }

        return $collection;
    }

    /**
     * Get the sub items from the clipboard.
     *
     * @param list<ItemInterface> $clipboardItems The clipboard items.
     * @param CollectionInterface $collection     The collection.
     *
     * @return list<ItemInterface>
     */
    protected function getSubClipboardItems(array $clipboardItems, CollectionInterface $collection)
    {
        $subClipboardItems = [];

        $modelIds = $collection->getModelIds();
        foreach ($clipboardItems as $clipboardItem) {
            $modelId = $clipboardItem->getModelId();
            if (null === $modelId || !\in_array($modelId->getId(), $modelIds)) {
                continue;
            }

            $subClipboardItems[] = $clipboardItem;
        }

        return $subClipboardItems;
    }

    /**
     * Set the sub items to the collection.
     *
     * @param ItemInterface        $previousItem      The previous item.
     * @param list<ItemInterface>  $subClipboardItems The sub clipboard items.
     * @param TPasteCollection     $collection        The collection.
     * @param EnvironmentInterface $environment       The environment.
     *
     * @return TPasteCollection
     *
     * @throws DcGeneralInvalidArgumentException Invalid configuration. Child condition must be defined.
     * @throws DcGeneralInvalidArgumentException Invalid model. Must be saved first.
     */
    protected function setSubItemsToCollection(
        ItemInterface $previousItem,
        array $subClipboardItems,
        array $collection,
        EnvironmentInterface $environment
    ) {
        if (empty($subClipboardItems)) {
            return $collection;
        }

        $dataProvider   = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $relationShip   = $dataDefinition->getModelRelationshipDefinition();
        $childCondition = $relationShip->getChildCondition($dataDefinition->getName(), $dataDefinition->getName());
        if (null === $childCondition) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. Child condition must be defined!'
            );
        }

        $previousModelId = $previousItem->getModelId();
        if (null === $previousModelId) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid model. Must be saved first!'
            );
        }

        $intoModelId = null;
        foreach ($subClipboardItems as $subClipboardItem) {
            $modelId = $subClipboardItem->getModelId();
            if (null === $modelId) {
                continue;
            }

            $pasteAfter =
                null !== $intoModelId
                    ? $intoModelId->getSerialized()
                    : $previousModelId->getSerialized();

            $intoModelId = $modelId;

            // Note: the original `$intoItem ? 'after' : 'into'` always evaluated to 'after'
            // because $intoItem was assigned immediately before the check.
            $collection[$modelId->getSerialized()] = [
                'item'       => $subClipboardItem,
                'pasteAfter' => $pasteAfter,
                'pasteMode'  => 'after'
            ];

            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
            assert($model instanceof ModelInterface);

            $itemCollection =
                $dataProvider->fetchAll($dataProvider->getEmptyConfig()->setFilter($childCondition->getFilter($model)));
            assert($itemCollection instanceof CollectionInterface);

            $collection = $this->setSubItemsToCollection(
                $subClipboardItem,
                $this->getSubClipboardItems($this->getClipboardItems($environment), $itemCollection),
                $collection,
                $environment
            );
        }

        return $collection;
    }

    /**
     * Add the event to the listeners for post duplicate model event.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    protected function addDispatchDuplicateModel(EnvironmentInterface $environment)
    {
        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->addListener(
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
     * @param TPasteItem           $collectionItem The collection item.
     * @param EnvironmentInterface $environment    The environment.
     *
     * @return void
     */
    protected function setParameterForPaste(array $collectionItem, EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $clipboardItem    = $collectionItem['item'];
        $clipboardModelId = $clipboardItem->getModelId();
        assert($clipboardModelId instanceof ModelIdInterface);

        $inputProvider->unsetParameter('after');
        $inputProvider->unsetParameter('into');
        $inputProvider->unsetParameter('source');
        $inputProvider->setParameter('source', $clipboardModelId->getSerialized());

        if (!$this->originalModel) {
            $inputProvider->setParameter($collectionItem['pasteMode'], $collectionItem['pasteAfter']);

            return;
        }

        $pasteAfterId = ModelId::fromSerialized((string) $collectionItem['pasteAfter']);
        if ($pasteAfterId->getId() !== $this->originalModel->getID()) {
            $inputProvider->setParameter($collectionItem['pasteMode'], $collectionItem['pasteAfter']);

            return;
        }

        assert($this->copiedModel instanceof ModelInterface);
        $copiedModelId = ModelId::fromModel($this->copiedModel);

        $inputProvider->setParameter($collectionItem['pasteMode'], $copiedModelId->getSerialized());
    }
}
