<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Item;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
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

        if (
            'cut' === $actionName
            || 'copy' === $actionName
            || 'deepcopy' === $actionName
        ) {
            $this->addToClipboard($event);
        }
    }

    /**
     * Handle clear clipboard action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    private function clearClipboard(ActionEvent $event)
    {
        $environment     = $event->getEnvironment();
        $eventDispatcher = $environment->getEventDispatcher();
        $clipboard       = $environment->getClipboard();
        $input           = $environment->getInputProvider();
        $modelId         = $input->getParameter('clipboard-item');

        if ($modelId) {
            $modelId = IdSerializer::fromSerialized($modelId);
            $clipboard->removeById($modelId);
        } else {
            $clipboard->clear();
        }
        $clipboard->saveTo($environment);

        $act           = $input->getParameter('original-act');
        $addToUrlEvent = new AddToUrlEvent('clipboard-item=&original-act=&act=' . $act);
        $eventDispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $addToUrlEvent);

        $redirectEvent = new RedirectEvent($addToUrlEvent->getUrl());
        $eventDispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, $redirectEvent);
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
        $modelIdRaw  = $input->getParameter('source');

        // Push some entry into clipboard.
        if ($modelIdRaw) {
            $modelId = IdSerializer::fromSerialized($modelIdRaw);

            $parentIdRaw = $input->getParameter('pid');
            if ($parentIdRaw) {
                $parentId = IdSerializer::fromSerialized($parentIdRaw);
            } else {
                $parentId = null;
            }

            switch ($actionName) {
                case 'cut':
                    $clipboardActionName = Item::CUT;
                    break;
                case 'copy':
                    $clipboardActionName = Item::COPY;
                    break;
                case 'deepcopy':
                    $clipboardActionName = Item::DEEP_COPY;
                    break;
                default:
                    return;
            }

            if ($clipboardActionName) {
                $item = new Item($clipboardActionName, $parentId, $modelId);

                // Let the clipboard save it's values persistent.
                // TODO remove clear and allow adding multiple items
                $clipboard->clear()->push($item)->saveTo($environment);

                ViewHelpers::redirectHome($environment);
            }
        }
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
        $filter->modelIsFromProvider($modelProviderName);
        if ($parentProviderName) {
            $filter->parentIsFromProvider($parentProviderName);
        } else {
            $filter->hasNoParent();
        }

        $options = array();
        foreach ($clipboard->fetch($filter) as $item) {
            $modelId           = $item->getModelId();
            $serializedModelId = $modelId->getSerialized();
            $dataProvider      = $environment->getDataProvider($modelId->getDataProviderName());
            $config            = $dataProvider->getEmptyConfig();
            $config->setId($modelId->getId());
            $model = $dataProvider->fetch($config);

            $formatModelLabelEvent = new FormatModelLabelEvent($environment, $model);
            $eventDispatcher->dispatch(DcGeneralEvents::FORMAT_MODEL_LABEL, $formatModelLabelEvent);
            $label = $formatModelLabelEvent->getLabel();
            $label = array_shift($label);
            $label = $label['content'];

            $options[$serializedModelId] = array(
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

        $template = new \BackendTemplate('dcbe_general_clipboard');

        $template->environment  = $environment;
        $template->options      = $options;
        $template->clearUrl     = $clearUrl;
        $template->clearItemUrl = $clearItemUrl;

        $event->setResponse($template->parse());
    }
}
