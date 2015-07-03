<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
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
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
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
            'create' === $actionName
            || 'cut' === $actionName
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

        if ('create' === $actionName) {
            $modelId = IdSerializer::fromValues(
                $environment->getDataDefinition()->getBasicDefinition()->getDataProvider(),
                null
            );
        } else {
            $modelIdRaw = $input->getParameter('source');
            $modelId    = IdSerializer::fromSerialized($modelIdRaw);
        }

        $parentIdRaw = $input->getParameter('pid');
        if ($parentIdRaw) {
            $parentId = IdSerializer::fromSerialized($parentIdRaw);
        } else {
            $parentId = null;
        }

        // Push some entry into clipboard.
        if ($modelId) {
            $clipboardActionName = $this->translateActionName($actionName);

            if ($clipboardActionName) {
                // Remove other create items, there can only be one create item in the clipboard or many others
                if (Item::CREATE === $clipboardActionName) {
                    $clipboard->clear();
                } else {
                    $filter = new Filter();
                    $filter->andActionIs(ItemInterface::CREATE);
                    $items = $clipboard->fetch($filter);
                    foreach ($items as $item) {
                        $clipboard->remove($item);
                    }
                }

                // Only push item to clipboard if manual sorting is used.
                if (Item::COPY === $clipboardActionName && !ViewHelpers::getManualSortingProperty($environment)) {
                    return;
                }

                // create the new item
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
        $filter->andModelIsFromProvider($modelProviderName);
        if ($parentProviderName) {
            $filter->andParentIsFromProvider($parentProviderName);
        } else {
            $filter->andHasNoParent();
        }

        $options = array();
        foreach ($clipboard->fetch($filter) as $item) {
            $modelId           = $item->getModelId();
            $serializedModelId = $modelId->getSerialized();
            $dataProvider      = $environment->getDataProvider($modelId->getDataProviderName());

            if ($modelId->getId()) {
                $config = $dataProvider->getEmptyConfig();
                $config->setId($modelId->getId());
                $model = $dataProvider->fetch($config);

                $formatModelLabelEvent = new FormatModelLabelEvent($environment, $model);
                $eventDispatcher->dispatch(DcGeneralEvents::FORMAT_MODEL_LABEL, $formatModelLabelEvent);
                $label = $formatModelLabelEvent->getLabel();
                $label = array_shift($label);
                $label = $label['content'];
            } else {
                $model = $dataProvider->getEmptyModel();
                $label = $environment->getTranslator()->translate('new.0', $modelId->getDataProviderName());
            }

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
