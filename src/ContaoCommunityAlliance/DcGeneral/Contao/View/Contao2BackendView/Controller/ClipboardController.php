<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller;

use Contao\BackendTemplate;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\UnsavedItem;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ClipboardController.
 */
class ClipboardController implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DcGeneralEvents::ACTION => array('handleAction'),
            DcGeneralEvents::VIEW   => array('handleView'),
        );
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
        $actionName = $event->getAction()->getName();

        if ('clear-clipboard' === $actionName) {
            $this->clearClipboard($event);
        }

        if (false === $this->checkPermission($event)) {
            $this->clearClipboard($event, false);

            $event->stopPropagation();

            return;
        }

        if ('create' === $actionName
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

        $environment     = $event->getEnvironment();
        $inputProvider   = $environment->getInputProvider();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        if (('create' === $actionName && true === $basicDefinition->isCreatable())
            || ('cut' === $actionName && true === $basicDefinition->isEditable())
            || (false === in_array($actionName, array('create', 'cut')))
        ) {
            return true;
        }

        $permissionMessage = 'You have no permission for model ' . $actionName .' ';
        switch ($actionName) {
            case 'create':
                $permissionMessage .= 'in ' . $dataDefinition->getName();
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
     *
     * @param bool        $redirect Redirect after clear the clipboard.
     *
     * @return void
     */
    private function clearClipboard(ActionEvent $event, $redirect = true)
    {
        $environment     = $event->getEnvironment();
        $eventDispatcher = $environment->getEventDispatcher();
        $clipboard       = $environment->getClipboard();
        $input           = $environment->getInputProvider();
        $clipboardId     = $input->getParameter('clipboard-item');

        if ($clipboardId) {
            $clipboard->removeByClipboardId($clipboardId);
        } else {
            $clipboard->clear();
        }
        $clipboard->saveTo($environment);

        if (false === $redirect) {
            return;
        }

        $act           = $input->getParameter('original-act');
        $addToUrlEvent = new AddToUrlEvent('clipboard-item=&original-act=&act=' . $act);
        $eventDispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $addToUrlEvent);

        $redirectEvent = new RedirectEvent($addToUrlEvent->getUrl());
        $eventDispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, $redirectEvent);
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
        $input       = $environment->getInputProvider();
        $clipboard   = $environment->getClipboard();

        $parentIdRaw = $input->getParameter('pid');
        if ($parentIdRaw) {
            $parentId = ModelId::fromSerialized($parentIdRaw);
        } else {
            $parentId = null;
        }

        $clipboardActionName = $this->translateActionName($actionName);
        if (!$clipboardActionName) {
            return;
        }

        if ('create' === $actionName) {
            if ($this->isAddingAllowed($environment)) {
                return;
            }

            $providerName = $environment->getDataDefinition()->getBasicDefinition()->getDataProvider();
            $item         = new UnsavedItem($clipboardActionName, $parentId, $providerName);

            // Remove other create items, there can only be one create item in the clipboard or many others
            $clipboard->clear();
        } else {
            $modelIdRaw = $input->getParameter('source');
            $modelId    = ModelId::fromSerialized($modelIdRaw);

            $filter = new Filter();
            $filter->andActionIs(ItemInterface::CREATE);
            $items = $clipboard->fetch($filter);
            foreach ($items as $item) {
                $clipboard->remove($item);
            }

            // Only push item to clipboard if manual sorting is used.
            if (Item::COPY === $clipboardActionName && !ViewHelpers::getManualSortingProperty($environment)) {
                return;
            }

            // create the new item
            $item = new Item($clipboardActionName, $parentId, $modelId);
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

        // No manual sorting property defined, no need to add it to the clipboard.
        // Or we already have an after or into attribute, a handler can pick it up.
        return (!ViewHelpers::getManualSortingProperty($environment)
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
        if (DcGeneralViews::CLIPBOARD !== $event->getViewName()) {
            return;
        }

        $environment        = $event->getEnvironment();
        $input              = $environment->getInputProvider();
        $clipboard          = $environment->getClipboard();
        $eventDispatcher    = $environment->getEventDispatcher();
        $basicDefinition    = $environment->getDataDefinition()->getBasicDefinition();
        $modelProviderName  = $basicDefinition->getDataProvider();
        $parentProviderName = $basicDefinition->getParentDataProvider();

        $filter = new Filter();
        $filter->andModelIsFromProvider($modelProviderName);
        if ($parentProviderName) {
            $filter->andParentIsFromProvider($parentProviderName);
        } else {
            $filter->andHasNoParent();
        }

        $options = array();
        foreach ($clipboard->fetch($filter) as $item) {
            $modelId      = $item->getModelId();
            $dataProvider = $environment->getDataProvider($item->getDataProviderName());

            if ($modelId) {
                $config = $dataProvider->getEmptyConfig();
                $config->setId($modelId->getId());
                $model = $dataProvider->fetch($config);

                // The model might have been deleted meanwhile.
                if (!$model) {
                    continue;
                }

                $formatModelLabelEvent = new FormatModelLabelEvent($environment, $model);
                $eventDispatcher->dispatch(DcGeneralEvents::FORMAT_MODEL_LABEL, $formatModelLabelEvent);
                $label = $formatModelLabelEvent->getLabel();
                $label = array_shift($label);
                $label = $label['content'];
            } else {
                $model = $dataProvider->getEmptyModel();
                $label = $environment->getTranslator()->translate('new.0', $item->getDataProviderName());
            }

            $options[$item->getClipboardId()] = array(
                'item'  => $item,
                'model' => $model,
                'label' => $label,
            );
        }

        $addToUrlEvent = new AddToUrlEvent('act=clear-clipboard&original-act=' . $input->getParameter('act'));
        $eventDispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $addToUrlEvent);
        $clearUrl = $addToUrlEvent->getUrl();

        $addToUrlEvent = new AddToUrlEvent(
            'clipboard-item=%id%&act=clear-clipboard&original-act=' . $input->getParameter('act')
        );
        $eventDispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $addToUrlEvent);
        $clearItemUrl = $addToUrlEvent->getUrl();

        $template = new BackendTemplate('dcbe_general_clipboard');

        $template->setData(
            array(
                'environment'  => $environment,
                'options'      => $options,
                'clearUrl'     => $clearUrl,
                'clearItemUrl' => $clearItemUrl
            )
        );

        $event->setResponse($template->parse());
    }
}
