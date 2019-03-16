<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     binron <rtb@gmx.ch>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Image;
use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\TranslatedToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class is an helper for rendering the operation buttons in the views.
 */
class ButtonRenderer
{
    /**
     * The ids of all circular contained models.
     *
     * @var string[]
     */
    private $circularModelIds;

    /**
     * The clipboard items in use.
     *
     * @var ItemInterface[]
     */
    private $clipboardItems;

    /**
     * The models for the clipboard items.
     *
     * @var CollectionInterface
     */
    private $clipboardModels;

    /**
     * The clipboard in use.
     *
     * @var CommandCollectionInterface
     */
    private $commands;

    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * The translator in use.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment.
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment      = $environment;
        $this->translator       = $environment->getTranslator();
        $this->eventDispatcher  = $environment->getEventDispatcher();
        $this->clipboardItems   = $this->calculateClipboardItems();
        $dataDefinition         = $environment->getDataDefinition();
        $this->commands         = $dataDefinition
            ->getDefinition(Contao2BackendViewDefinitionInterface::NAME)
            ->getModelCommands();
        $controller             = $environment->getController();
        $this->clipboardModels  = $controller->getModelsFromClipboardItems($this->clipboardItems);
        $this->circularModelIds = [];

        // We must only check for CUT operation here as pasting copy'ed parents is allowed.
        $cutItems = \array_filter($this->clipboardItems, function ($item) {
            /** @var ItemInterface $item */
            return $item->getAction() === $item::CUT;
        });
        $cutModels = $controller->getModelsFromClipboardItems($cutItems);
        $collector = new ModelCollector($environment);
        foreach ($cutModels as $model) {
            $providerName = $model->getProviderName();
            foreach ($collector->collectChildrenOf($model) as $subModel) {
                $this->circularModelIds[] = ModelId::fromValues($providerName, $subModel)->getSerialized();
            }
        }
    }

    /**
     * Render the operation buttons for the passed models.
     *
     * @param CollectionInterface $collection The collection containing the models.
     *
     * @return void
     */
    public function renderButtonsForCollection(CollectionInterface $collection)
    {
        // Generate buttons.
        foreach ($collection as $i => $model) {
            $previous = $collection->get($i - 1);
            $next     = $collection->get($i + 1);
            /** @var ModelInterface $model */
            $this->renderButtonsFor($model, $previous, $next);
        }
    }

    /**
     * Render the operation buttons for the passed model.
     *
     * @param ModelInterface $model    The model to render the buttons for.
     * @param ModelInterface $previous The previous model in the collection.
     * @param ModelInterface $next     The next model in the collection.
     *
     * @return void
     */
    private function renderButtonsFor(
        ModelInterface $model,
        ModelInterface $previous = null,
        ModelInterface $next = null
    ) {
        $modelId = ModelId::fromModel($model)->getSerialized();

        if ($this->clipboardItems) {
            $isCircular = \in_array(ModelId::fromModel($model)->getSerialized(), $this->circularModelIds);
        } else {
            $isCircular = false;
        }
        $childIds = $this->getChildIds($model);

        $buttons = [];
        foreach ($this->commands->getCommands() as $command) {
            $buttons[$command->getName()] =
                $this->buildCommand($command, $model, $previous, $next, $isCircular, $childIds);
        }

        if ($this->hasPasteNewButton()) {
            $buttons['pasteNew'] = $this->renderPasteNewFor($modelId);
        }

        // Add paste into/after icons.
        if ($this->hasPasteButtons()) {
            $urlAfter = $this->addToUrl(\sprintf('act=paste&after=%s&', $modelId));
            $urlInto  = $this->addToUrl(\sprintf('act=paste&into=%s&', $modelId));


            $buttonEvent = new GetPasteButtonEvent($this->environment);
            $buttonEvent
                ->setModel($model)
                ->setCircularReference($isCircular)
                ->setPrevious($previous)
                ->setNext($next)
                ->setHrefAfter($urlAfter)
                ->setHrefInto($urlInto)
                // Check if the id is in the ignore list.
                ->setPasteAfterDisabled($isCircular)
                ->setPasteIntoDisabled($isCircular)
                ->setContainedModels($this->clipboardModels);
            $this->eventDispatcher->dispatch(GetPasteButtonEvent::NAME, $buttonEvent);

            $buttons['pasteafter'] = $this->renderPasteAfterButton($buttonEvent);
            if ($this->isHierarchical()) {
                $buttons['pasteinto'] = $this->renderPasteIntoButton($buttonEvent);
            }
        }

        $model->setMeta(
            $model::OPERATION_BUTTONS,
            \implode(' ', $buttons)
        );
    }

    /**
     * If the view is hierarchical.
     *
     * @return bool
     */
    private function isHierarchical()
    {
        $environment     = $this->environment;
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        return $basicDefinition::MODE_HIERARCHICAL === $basicDefinition->getMode();
    }

    /**
     * Determining if into and after buttons shall be examined.
     *
     * @return bool
     */
    private function hasPasteButtons()
    {
        return ((true === (bool) ViewHelpers::getManualSortingProperty($this->environment))
                && false === empty($this->clipboardItems));
    }

    /**
     * Determining if paste new buttons shall be examined.
     *
     * @return bool
     */
    private function hasPasteNewButton()
    {
        $environment     = $this->environment;
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        return ((true === (bool) ViewHelpers::getManualSortingProperty($environment))
                && (true === empty($this->clipboardItems))
                && (false === $this->isHierarchical())
                && ($basicDefinition->isEditable() && $basicDefinition->isCreatable()));
    }

    /**
     * Render a command button.
     *
     * @param CommandInterface $command             The command to render the button for.
     * @param ModelInterface   $model               The model to which the command shall get applied.
     * @param ModelInterface   $previous            The previous model in the collection.
     * @param ModelInterface   $next                The next model in the collection.
     * @param bool             $isCircularReference Determinator if there exists a circular reference between the model
     *                                              and the model(s) contained in the clipboard.
     * @param string[]         $childIds            The ids of all child models.
     *
     * @return string
     */
    private function buildCommand($command, $model, $previous, $next, $isCircularReference, $childIds)
    {
        $extra      = (array) $command->getExtra();
        $attributes = '';

        if (!empty($extra['attributes'])) {
            $attributes .= \sprintf($extra['attributes'], $model->getID());
        }
        $label = $this->getCommandLabel($command);
        $title = \sprintf($this->translate($command->getDescription()), $model->getID());
        $icon  = $extra['icon'];

        if ($command instanceof ToggleCommandInterface) {
            $iconDisabled = isset($extra['icon_disabled'])
                ? $extra['icon_disabled']
                : 'invisible.svg';

            $attributes .= \sprintf(
                ' onclick="Backend.getScrollOffset(); return BackendGeneral.toggleVisibility(this, \'%s\', \'%s\');"',
                $icon,
                $iconDisabled
            );

            if (!$this->isTogglerInActiveState($command, $model)) {
                $icon = $iconDisabled;
            }
        }

        $href = $this->calculateHref($command, $model);

        $buttonEvent = new GetOperationButtonEvent($this->environment);
        $buttonEvent
            ->setKey($command->getName())
            ->setCommand($command)
            ->setObjModel($model)
            ->setAttributes($attributes)
            ->setLabel($label)
            ->setTitle($title)
            ->setHref($href)
            ->setChildRecordIds($childIds)
            ->setCircularReference($isCircularReference)
            ->setPrevious($previous)
            ->setNext($next)
            ->setDisabled($command->isDisabled());
        $this->eventDispatcher->dispatch(GetOperationButtonEvent::NAME, $buttonEvent);

        if (null !== ($html = $buttonEvent->getHtml())) {
            // If the event created a button, use it.
            return \trim($html);
        }

        if ($buttonEvent->isDisabled()) {
            $iconDisabledSuffix = '_1';

            // Check wheter icon is part of contao
            if ($icon !== Image::getPath($icon)) {
                $iconDisabledSuffix = '_';
            }

            return $this->renderImageAsHtml(
                \substr_replace($icon, $iconDisabledSuffix, \strrpos($icon, '.'), 0),
                $buttonEvent->getLabel()
            );
        }

        return \sprintf(
            ' <a class="%s" href="%s" title="%s" %s>%s</a>',
            $command->getName(),
            $buttonEvent->getHref(),
            StringUtil::specialchars($buttonEvent->getTitle()),
            \ltrim($buttonEvent->getAttributes()),
            $this->renderImageAsHtml($icon, $buttonEvent->getLabel())
        );
    }

    /**
     * Recursively determine all child ids of the passed model.
     *
     * @param ModelInterface $model The model to fetch the ids from.
     *
     * @return string[]
     */
    private function getChildIds(ModelInterface $model)
    {
        if (null === ($childCollections = $model->getMeta($model::CHILD_COLLECTIONS))) {
            return [];
        }

        $result = [ModelId::fromModel($model)->getSerialized()];
        foreach ($childCollections as $collection) {
            foreach ($collection as $child) {
                $result += $this->getChildIds($child);
            }
        }

        return $result;
    }

    /**
     * Calculate the special parameters for certain operations.
     *
     * @param CommandInterface $command           The command.
     * @param string           $serializedModelId The model id to use.
     *
     * @return string[]
     */
    private function calculateParameters(CommandInterface $command, $serializedModelId)
    {
        $parameters = (array) $command->getParameters();
        if ($command instanceof ToggleCommandInterface) {
            // Toggle has to trigger the javascript.
            $parameters['act'] = $command->getName();
            $parameters['id']  = $serializedModelId;

            return $parameters;
        }
        if (($command instanceof CutCommandInterface) || ($command instanceof CopyCommandInterface)) {
            // Cut & copy need some special information.
            $parameters        = [];
            $parameters['act'] = $command->getName();

            $inputProvider = $this->environment->getInputProvider();
            // If we have a pid add it, used for mode 4 and all parent -> current views.
            if ($inputProvider->hasParameter('pid')) {
                $parameters['pid'] = $inputProvider->getParameter('pid');
            }

            // Source is the id of the element which should move.
            $parameters['source'] = $serializedModelId;

            return $parameters;
        }

        $extra   = (array) $command->getExtra();
        $idParam = isset($extra['idparam']) ? $extra['idparam'] : null;
        if ($idParam) {
            $parameters[$idParam] = $serializedModelId;
        } else {
            $parameters['id'] = $serializedModelId;
        }

        return $parameters;
    }

    /**
     * Render the "paste new" button.
     *
     * @param string $modelId The model id for which to create the paste new button.
     *
     * @return string
     */
    private function renderPasteNewFor($modelId)
    {
        $label = \sprintf($this->translate('pastenew.1'), ModelId::fromSerialized($modelId)->getId());
        return \sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $this->addToUrl('act=create&amp;after=' . $modelId),
            StringUtil::specialchars($label),
            $this->renderImageAsHtml('new.svg', $label)
        );
    }

    /**
     * Render the paste into button.
     *
     * @param GetPasteButtonEvent $event The event that has been triggered.
     *
     * @return string
     */
    private function renderPasteIntoButton(GetPasteButtonEvent $event)
    {
        if (null !== ($value = $event->getHtmlPasteInto())) {
            return $value;
        }

        $label = $this->translate('pasteinto.0');
        if ($event->isPasteIntoDisabled()) {
            return $this->renderImageAsHtml('pasteinto_.svg', $label, 'class="blink"');
        }

        if ('pasteinto.1' !== ($opDesc = $this->translate('pasteinto.1'))) {
            $title = \sprintf($opDesc, $event->getModel()->getId());
        } else {
            $title = \sprintf('%s id %s', $label, $event->getModel()->getId());
        }

        return \sprintf(
            ' <a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $event->getHrefInto(),
            StringUtil::specialchars($title),
            $this->renderImageAsHtml('pasteinto.svg', $label, 'class="blink"')
        );
    }

    /**
     * Render the paste after button.
     *
     * @param GetPasteButtonEvent $event The event that has been triggered.
     *
     * @return string
     */
    private function renderPasteAfterButton(GetPasteButtonEvent $event)
    {
        if (null !== ($value = $event->getHtmlPasteAfter())) {
            return $value;
        }

        $label = $this->translate('pasteafter.0');
        if ($event->isPasteAfterDisabled()) {
            return $this->renderImageAsHtml('pasteafter_.svg', $label, 'class="blink"');
        }

        if ('pasteafter.1' !== ($opDesc = $this->translate('pasteafter.1'))) {
            $title = \sprintf($opDesc, $event->getModel()->getId());
        } else {
            $title = \sprintf('%s id %s', $label, $event->getModel()->getId());
        }

        return \sprintf(
            ' <a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $event->getHrefAfter(),
            StringUtil::specialchars($title),
            $this->renderImageAsHtml('pasteafter.svg', $label, 'class="blink"')
        );
    }

    /**
     * Calculate all clipboard items for the current view.
     *
     * @return ItemInterface[]
     */
    private function calculateClipboardItems()
    {
        $dataDefinition  = $this->environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();
        $clipboard       = $this->environment->getClipboard();

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        if ($parentDataProviderName = $basicDefinition->getParentDataProvider()) {
            $filter->andParentIsFromProvider($parentDataProviderName);
        } else {
            $filter->andHasNoParent();
        }

        return $clipboard->fetch($filter);
    }

    /**
     * Translate a string via the translator.
     *
     * @param string $path The path within the translation where the string can be found.
     *
     * @return string
     */
    protected function translate($path)
    {
        $value = $this->translator->translate($path, $this->environment->getDataDefinition()->getName());
        if ($path !== $value) {
            return $value;
        }

        return $this->translator->translate($path);
    }

    /**
     * Render an image as HTML string.
     *
     * @param string $src        The image path.
     * @param string $alt        An optional alt attribute.
     * @param string $attributes A string of other attributes.
     *
     * @return string
     */
    private function renderImageAsHtml($src, $alt, $attributes = '')
    {
        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $this->eventDispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent($src, $alt, $attributes)
        );

        return $imageEvent->getHtml();
    }

    /**
     * Add some url parameters to the current URL.
     *
     * @param string $parameters The parameters to add.
     *
     * @return string
     */
    private function addToUrl($parameters)
    {
        /** @var AddToUrlEvent $urlAfter */
        $urlAfter = $this->eventDispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, new AddToUrlEvent($parameters));

        return $urlAfter->getUrl();
    }

    /**
     * Determine the toggle state of a toggle command.
     *
     * @param ToggleCommandInterface $command The toggle command.
     * @param ModelInterface         $model   The model in scope.
     *
     * @return bool
     */
    private function isTogglerInActiveState($command, $model)
    {
        $dataProvider = $this->environment->getDataProvider($model->getProviderName());
        if ($command instanceof TranslatedToggleCommandInterface
            && $dataProvider instanceof MultiLanguageDataProviderInterface
        ) {
            $language = $dataProvider->getCurrentLanguage();
            $dataProvider->setCurrentLanguage($command->getLanguage());
            $propModel = $dataProvider->fetch(
                $dataProvider
                    ->getEmptyConfig()
                    ->setId($model->getId())
                    ->setFields([$command->getToggleProperty()])
            );
            $dataProvider->setCurrentLanguage($language);
        } else {
            $propModel = $model;
        }

        if ($command->isInverse()) {
            return !$propModel->getProperty($command->getToggleProperty());
        }

        return (bool) $propModel->getProperty($command->getToggleProperty());
    }

    /**
     * Calculate the href for a command.
     *
     * @param CommandInterface $command The command.
     * @param ModelInterface   $model   The current model.
     *
     * @return string
     */
    private function calculateHref(CommandInterface $command, $model)
    {
        $modelId    = ModelId::fromModel($model)->getSerialized();
        $parameters = $this->calculateParameters($command, $modelId);
        $href       = '';
        foreach ($parameters as $key => $value) {
            $href .= \sprintf('&%s=%s', $key, $value);
        }

        return $this->addToUrl($href);
    }

    /**
     * Get the correct label for a command button.
     *
     * @param CommandInterface $command The command.
     *
     * @return string
     */
    private function getCommandLabel(CommandInterface $command)
    {
        if (null === $label = $command->getLabel()) {
            $label = $command->getName();
        }

        $label = $this->translate($label);

        if (\is_array($label)) {
            $label = $label[0];

            return $label;
        }

        return $label;
    }
}
