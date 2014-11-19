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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\DcGeneralViews;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;

/**
 * Class ClipboardView
 */
class ClipboardView
{
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

        $options = array();
        foreach ($clipboard->fetch($modelProviderName, $parentProviderName) as $item) {
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

        $template = new \BackendTemplate('dcbe_general_clipboard');

        $template->environment = $environment;
        $template->options     = $options;
        $template->clearUrl    = $addToUrlEvent->getUrl();

        $event->setResponse($template->parse());
    }
}
