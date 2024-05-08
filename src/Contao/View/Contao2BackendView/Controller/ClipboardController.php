<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.x
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\UnsavedItem;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_shift;
use function in_array;
use function is_string;
use function parse_str;
use function sprintf;

/**
 * Class ClipboardController.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ClipboardController implements EventSubscriberInterface
{
    /**
     * The request mode determinator.
     *
     * @var RequestScopeDeterminator
     */
    private RequestScopeDeterminator $scopeDeterminator;

    /**
     * ClipboardController constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            DcGeneralEvents::ACTION => ['handleAction'],
            DcGeneralEvents::VIEW   => ['handleView']
        ];
    }

    /**
     * Handle action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleAction(ActionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        if ('clear-clipboard' === ($actionName = $event->getAction()->getName())) {
            $this->clearClipboard($event);
        }

        if (false === $this->checkPermission($event)) {
            $this->clearClipboard($event, false);

            $event->stopPropagation();

            return;
        }

        if (
            'create' === $actionName
            || 'cut' === $actionName
            || 'copy' === $actionName
            || 'deepcopy' === $actionName
        ) {
            $this->addToClipboard($event);
        }
    }

    /**
     * Check if permission for action.
     *
     * @param ActionEvent $event The event.
     *
     * @return bool
     */
    private function checkPermission(ActionEvent $event)
    {
        $actionName = $event->getAction()->getName();

        $environment = $event->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $basicDefinition = $definition->getBasicDefinition();

        if (
            (('create' === $actionName) && (true === $basicDefinition->isCreatable()))
            || (('cut' === $actionName) && (true === $basicDefinition->isEditable()))
            || (false === in_array($actionName, ['create', 'cut']))
        ) {
            return true;
        }

        $permissionMessage = 'You have no permission for model ' . $actionName . ' ';
        switch ($actionName) {
            case 'create':
                $permissionMessage .= 'in ' . $definition->getName();
                break;

            case 'cut':
                $permissionMessage .= $inputProvider->getParameter('source');
                break;

            default:
        }

        $event->setResponse(
            sprintf(
                '<div style="text-align:center; font-weight:bold; padding:40px;">%s.</div>',
                $permissionMessage
            )
        );

        return false;
    }

    /**
     * Handle clear clipboard action.
     *
     * @param ActionEvent $event    The action event.
     * @param bool        $redirect Redirect after clear the clipboard.
     *
     * @return void
     */
    private function clearClipboard(ActionEvent $event, $redirect = true)
    {
        $environment = $event->getEnvironment();

        $eventDispatcher = $environment->getEventDispatcher();
        assert($eventDispatcher instanceof EventDispatcherInterface);

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        if ($clipboardId = $input->getParameter('clipboard-item')) {
            $clipboard->removeByClipboardId($clipboardId);
        } else {
            $clipboard->clear();
        }
        $clipboard->saveTo($environment);

        if (false === $redirect) {
            return;
        }

        $addToUrlEvent = new AddToUrlEvent('clipboard-item=&original-act=&act=' . $input->getParameter('original-act'));
        $eventDispatcher->dispatch($addToUrlEvent, ContaoEvents::BACKEND_ADD_TO_URL);

        $url = new UrlBuilder($addToUrlEvent->getUrl());
        parse_str($url->getQueryString() ?? '', $parameters);
        foreach ($parameters as $name => $value) {
            if ('' === $value) {
                $url->unsetQueryParameter($name);
            }
        }

        $redirectEvent = new RedirectEvent($url->getUrl());
        $eventDispatcher->dispatch($redirectEvent, ContaoEvents::CONTROLLER_REDIRECT);
    }

    /**
     * Translate an action name to a clipboard action name.
     *
     * @param string $actionName The action name to translate.
     *
     * @return null|string
     */
    private function translateActionName($actionName)
    {
        switch ($actionName) {
            case 'create':
                return Item::CREATE;
            case 'cut':
                return Item::CUT;
            case 'copy':
                return Item::COPY;
            case 'deepcopy':
                return Item::DEEP_COPY;
            default:
        }
        return null;
    }

    /**
     * Handle "old" add to clipboard actions.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    private function addToClipboard(ActionEvent $event)
    {
        $actionName  = $event->getAction()->getName();
        $environment = $event->getEnvironment();

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);


        $parentIdRaw = $input->getParameter('pid');
        if ($parentIdRaw) {
            $parentId = ModelId::fromSerialized($parentIdRaw);
        } else {
            $parentId = null;
        }

        if (null === ($clipboardActionName = $this->translateActionName($actionName))) {
            return;
        }

        if ('create' === $actionName) {
            if ($this->isAddingAllowed($environment)) {
                return;
            }

            $definition = $environment->getDataDefinition();
            assert($definition instanceof ContainerInterface);

            $providerName = $definition->getBasicDefinition()->getDataProvider();
            assert(is_string($providerName));

            $item = new UnsavedItem($clipboardActionName, $parentId, $providerName);

            // Remove other create items, there can only be one create item in the clipboard or many others.
            $clipboard->clear();
        } else {
            $modelIdRaw = $input->getParameter('source');
            $modelId    = ModelId::fromSerialized($modelIdRaw);

            // If edit several don´t remove items from the clipboard.
            $this->removeItemsFromClipboard($event);

            // Only push item to clipboard if manual sorting is used.
            if (Item::COPY === $clipboardActionName && null === ViewHelpers::getManualSortingProperty($environment)) {
                return;
            }

            // create the new item
            $item = new Item($clipboardActionName, $parentId, $modelId);
        }

        // If edit several don´t redirect do home and push item to the clipboard.
        if ('select' === $input->getParameter('act')) {
            $clipboard->push($item)->saveTo($environment);

            return;
        }

        // Let the clipboard save it's values persistent.
        $clipboard->clear()->push($item)->saveTo($environment);

        ViewHelpers::redirectHome($environment);
    }

    /**
     * Is adding to the clipboard allowed.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    protected function isAddingAllowed(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        // No manual sorting property defined, no need to add it to the clipboard.
        // Or we already have an after or into attribute, a handler can pick it up.
        return (null === ViewHelpers::getManualSortingProperty($environment)
                || $inputProvider->hasParameter('after')
                || $inputProvider->hasParameter('into')
        );
    }

    /**
     * Handle view.
     *
     * @param ViewEvent $event The view event.
     *
     * @return void
     */
    public function handleView(ViewEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        if (DcGeneralViews::CLIPBOARD !== $event->getViewName()) {
            return;
        }

        $environment = $event->getEnvironment();

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        $eventDispatcher = $environment->getEventDispatcher();
        assert($eventDispatcher instanceof EventDispatcherInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();

        $dataProvider = $basicDefinition->getDataProvider();
        assert(is_string($dataProvider));

        $filter = new Filter();
        $filter->andModelIsFromProvider($dataProvider);
        if (null !== ($parentProviderName = $basicDefinition->getParentDataProvider())) {
            $filter->andParentIsFromProvider($parentProviderName);
        } else {
            $filter->andHasNoParent();
        }

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $options = [];
        foreach ($clipboard->fetch($filter) as $item) {
            $modelId      = $item->getModelId();
            $dataProvider = $environment->getDataProvider($item->getDataProviderName());
            assert($dataProvider instanceof DataProviderInterface);

            if ($modelId) {
                $config = $dataProvider->getEmptyConfig();
                $config->setId($modelId->getId());
                $model = $dataProvider->fetch($config);

                // The model might have been deleted meanwhile.
                if (!$model) {
                    continue;
                }

                $formatModelLabel = new FormatModelLabelEvent($environment, $model);
                $eventDispatcher->dispatch($formatModelLabel, DcGeneralEvents::FORMAT_MODEL_LABEL);
                $label = $formatModelLabel->getLabel();
                $label = array_shift($label);
                $label = $label['content'];
            } else {
                $model = $dataProvider->getEmptyModel();

                $translator = $environment->getTranslator();
                assert($translator instanceof TranslatorInterface);

                $label = $translator->translate('new.label', $item->getDataProviderName());
            }

            $options[$item->getClipboardId()] = ['item'  => $item, 'model' => $model, 'label' => $label];
        }

        $inputAction   = $input->getParameter('act');
        $addToUrlEvent = new AddToUrlEvent('act=clear-clipboard&original-act=' . $inputAction);
        $eventDispatcher->dispatch($addToUrlEvent, ContaoEvents::BACKEND_ADD_TO_URL);
        $clearUrl = $addToUrlEvent->getUrl();

        $addToUrlEvent = new AddToUrlEvent(
            'clipboard-item=%id%&act=clear-clipboard&original-act=' . $inputAction
        );
        $eventDispatcher->dispatch($addToUrlEvent, ContaoEvents::BACKEND_ADD_TO_URL);
        $clearItemUrl = $addToUrlEvent->getUrl();

        $template = new ContaoBackendViewTemplate('dcbe_general_clipboard');
        $template
            ->set('environment', $environment)
            ->set('options', $options)
            ->set('clearUrl', $clearUrl)
            ->set('clearItemUrl', $clearItemUrl);

        $event->setResponse($template->parse());
    }

    /**
     * Remove items from clipboard.
     * If action is select don´t remove anything.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     */
    private function removeItemsFromClipboard(ActionEvent $event)
    {
        $environment   = $event->getEnvironment();
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if ('select' === $inputProvider->getParameter('act')) {
            return;
        }

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $filter = new Filter();
        $filter->andActionIs(ItemInterface::CREATE);
        $items = $clipboard->fetch($filter);
        foreach ($items as $item) {
            $clipboard->remove($item);
        }
    }
}
