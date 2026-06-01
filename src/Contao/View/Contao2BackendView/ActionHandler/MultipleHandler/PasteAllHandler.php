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
 * @return void
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

        /** @psalm-suppress MixedAssignment */
        foreach ($collection as $collectionItem) {
            /** @psalm-suppress MixedArgument */
            $this->setParameterForPaste($collectionItem, $environment);

            $this->callAction($environment, 'paste');

            /** @psalm-suppress MixedAssignment */
            $clipboardItem = $collectionItem['item'];
            /** @psalm-suppress MixedMethodCall */
            $clipboardItemModelId = $clipboardItem->getModelId();
            /** @psalm-suppress MixedArgument */
            $clipboard->removeById($clipboardItemModelId);
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
     * @return array
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
     * @return array
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
     * @return array
     */
    protected function getFlatCollection(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $previousItem = null;
        $collection   = [];
        /** @psalm-suppress MixedAssignment */
        foreach ($this->getClipboardItems($environment) as $clipboardItem) {
            /** @psalm-suppress MixedMethodCall */
            if ('create' === $clipboardItem->getAction()) {
                continue;
            }
            /** @psalm-suppress MixedMethodCall */
            $previousSerialized = null !== $previousItem ? $previousItem->getModelId()->getSerialized() : null;
            /** @psalm-suppress MixedAssignment */
            $pasteAfter = $previousSerialized ?? $inputProvider->getParameter('after');

            /** @psalm-suppress MixedMethodCall */
            $clipboardSerialized = $clipboardItem->getModelId()->getSerialized();
            /** @psalm-suppress MixedArrayOffset */
            $collection[$clipboardSerialized] = [
                'item'       => $clipboardItem,
                'pasteAfter' => $pasteAfter,
                'pasteMode'  => 'after'
            ];

            /** @psalm-suppress MixedAssignment */
            $previousItem = $clipboardItem;
        }

        return $collection;
    }

    /**
     * Get hierarchy collection.
     *
     * @param array                $clipboardItems The clipboard items.
     * @param EnvironmentInterface $environment    The environment.
     *
     * @return array
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

        $previousItem = null;
        /** @psalm-suppress MixedAssignment */
        foreach ($clipboardItems as $clipboardItem) {
            /** @psalm-suppress MixedAssignment */
            /** @psalm-suppress MixedMethodCall */
            $modelId = $clipboardItem->getModelId();
            /** @psalm-suppress MixedArgument */
            /** @psalm-suppress MixedMethodCall */
            if (!$modelId || \array_key_exists($modelId->getSerialized(), $collection)) {
                continue;
            }

            $pasteMode  = null !== $previousItem ? 'after' : $originalPasteMode;
            /** @psalm-suppress MixedMethodCall */
            $previousSerialized = null !== $previousItem ? $previousItem->getModelId()->getSerialized() : null;
            /** @psalm-suppress MixedAssignment */
            $pasteAfter = $previousSerialized ?? $inputProvider->getParameter($pasteMode);

            /** @psalm-suppress MixedMethodCall */
            $modelSerialized = $modelId->getSerialized();
            /** @psalm-suppress MixedArrayOffset */
            $collection[$modelSerialized] = [
                'item'       => $clipboardItem,
                'pasteAfter' => $pasteAfter,
                'pasteMode'  => $pasteMode
            ];

            /** @psalm-suppress MixedAssignment */
            $previousItem = $clipboardItem;

            /** @psalm-suppress MixedMethodCall */
            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
            assert($model instanceof ModelInterface);

            $itemCollection =
                $dataProvider->fetchAll($dataProvider->getEmptyConfig()->setFilter($childCondition->getFilter($model)));
            assert($itemCollection instanceof CollectionInterface);

            /** @psalm-suppress MixedArgument */
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
     * @param array               $clipboardItems The clipboard items.
     * @param CollectionInterface $collection     The collection.
     *
     * @return array
     */
    protected function getSubClipboardItems(array $clipboardItems, CollectionInterface $collection)
    {
        $subClipboardItems = [];

        $modelIds = $collection->getModelIds();
        /** @psalm-suppress MixedAssignment */
        foreach ($clipboardItems as $clipboardItem) {
            /** @psalm-suppress MixedMethodCall */
            if (!\in_array($clipboardItem->getModelId()->getId(), $modelIds)) {
                continue;
            }

            /** @psalm-suppress MixedAssignment */
            $subClipboardItems[] = $clipboardItem;
        }

        return $subClipboardItems;
    }

    /**
     * Set the sub items to the collection.
     *
     * @param ItemInterface        $previousItem      The previous item.
     * @param array                $subClipboardItems The sub clipboard items.
     * @param array                $collection        The collection.
     * @param EnvironmentInterface $environment       The environment.
     *
     * @return array
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

        $intoItem = null;
        /** @psalm-suppress MixedAssignment */
        foreach ($subClipboardItems as $subClipboardItem) {
            /** @psalm-suppress MixedMethodCall */
            /** @psalm-suppress MixedAssignment */
            $modelId = $subClipboardItem->getModelId();

            /** @psalm-suppress MixedMethodCall */
            $intoSerialized = null !== $intoItem ? $intoItem->getModelId()->getSerialized() : null;
            /** @psalm-suppress MixedAssignment */
            $pasteAfter = $intoSerialized ?? $previousModelId->getSerialized();

            /** @psalm-suppress MixedAssignment */
            $intoItem = $subClipboardItem;

            /** @psalm-suppress MixedMethodCall */
            $modelSerialized = $modelId->getSerialized();
            /** @psalm-suppress MixedArrayOffset */
            $collection[$modelSerialized] = [
                'item'       => $subClipboardItem,
                'pasteAfter' => $pasteAfter,
                'pasteMode'  => $intoItem ? 'after' : 'into'
            ];

            /** @psalm-suppress MixedMethodCall */
            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
            assert($model instanceof ModelInterface);

            $itemCollection =
                $dataProvider->fetchAll($dataProvider->getEmptyConfig()->setFilter($childCondition->getFilter($model)));
            assert($itemCollection instanceof CollectionInterface);

            /** @psalm-suppress MixedArgument */
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
     * @param array                $collectionItem The collection item.
     * @param EnvironmentInterface $environment    The environment.
     *
     * @return void
     */
    protected function setParameterForPaste(array $collectionItem, EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        /** @psalm-suppress MixedAssignment */
        $clipboardItem = $collectionItem['item'];

        $inputProvider->unsetParameter('after');
        $inputProvider->unsetParameter('into');
        $inputProvider->unsetParameter('source');
        /** @psalm-suppress MixedMethodCall */
        $inputProvider->setParameter('source', $clipboardItem->getModelId()->getSerialized());

        if (!$this->originalModel) {
            /** @psalm-suppress MixedArgument */
            $inputProvider->setParameter($collectionItem['pasteMode'], $collectionItem['pasteAfter']);

            return;
        }

        $pasteAfterId = ModelId::fromSerialized((string) $collectionItem['pasteAfter']);
        if ($pasteAfterId->getId() !== $this->originalModel->getID()) {
            /** @psalm-suppress MixedArgument */
            $inputProvider->setParameter($collectionItem['pasteMode'], $collectionItem['pasteAfter']);

            return;
        }

        assert($this->copiedModel instanceof ModelInterface);
        $copiedModelId = ModelId::fromModel($this->copiedModel);

        /** @psalm-suppress MixedArgument */
        $inputProvider->setParameter($collectionItem['pasteMode'], $copiedModelId->getSerialized());
    }
}
