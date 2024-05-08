<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * This class handle for add the default buttons for the select mode.
 */
class SelectModeButtonsListener
{
    /**
     * Handle event for add the default buttons for the select mode.
     *
     * @param GetSelectModeButtonsEvent $event The event.
     *
     * @return void
     */
    public function handleEvent(GetSelectModeButtonsEvent $event)
    {
        $environment = $event->getEnvironment();

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();
        $buttons         = [];

        $confirmMessage = \htmlentities(
            \sprintf(
                '<h2 class="tl_error">%s</h2>' .
                '<p></p>' .
                '<div class="tl_submit_container">' .
                '<input type="submit" name="close" class="%s" value="%s" onclick="%s">' .
                '</div>',
                StringUtil::specialchars($translator->translate('nothingSelect', 'dc-general')),
                'tl_submit',
                StringUtil::specialchars($translator->translate('close', 'dc-general')),
                'this.blur(); BackendGeneral.hideMessage(); return false;'
            )
        );
        $onClick        = 'BackendGeneral.confirmSelectOverrideEditAll(this, \'models[]\', \''
                          . $confirmMessage . '\'); return false;';

        $input = '<input type="submit" name="%s" id="%s" class="tl_submit" accesskey="%s" value="%s" onclick="%s">';

        if ($basicDefinition->isDeletable()) {
            $onClickDelete = \sprintf(
                'BackendGeneral.confirmSelectDeleteAll(this, \'%s\', \'%s\', \'%s\', \'%s\', \'%s\'); return false;',
                'models[]',
                $confirmMessage,
                StringUtil::specialchars($translator->translate('delAllConfirm', 'dc-general')),
                StringUtil::specialchars($translator->translate('confirmOk', 'dc-general')),
                StringUtil::specialchars($translator->translate('confirmAbort', 'dc-general'))
            );

            $buttons['delete'] = \sprintf(
                $input,
                'delete',
                'delete',
                'd',
                StringUtil::specialchars($translator->translate('deleteSelected', 'dc-general')),
                $onClickDelete
            );
        }

        $sortingProperty = ViewHelpers::getManualSortingProperty($event->getEnvironment());
        if (null !== $sortingProperty && $basicDefinition->isEditable()) {
            $buttons['cut'] = \sprintf(
                $input,
                'cut',
                'cut',
                's',
                StringUtil::specialchars($translator->translate('moveSelected', 'dc-general')),
                $onClick
            );
        }

        if ($basicDefinition->isCreatable()) {
            $buttons['copy'] = \sprintf(
                $input,
                'copy',
                'copy',
                'c',
                StringUtil::specialchars($translator->translate('copySelected', 'dc-general')),
                $onClick
            );
        }

        if ($basicDefinition->isEditable()) {
            $buttons['override'] = \sprintf(
                $input,
                'override',
                'override',
                'v',
                StringUtil::specialchars($translator->translate('overrideSelected', 'dc-general')),
                $onClick
            );

            $buttons['edit'] = \sprintf(
                $input,
                'edit',
                'edit',
                's',
                StringUtil::specialchars($translator->translate('editSelected', 'dc-general')),
                $onClick
            );
        }

        $event->setButtons($buttons);
    }
}
