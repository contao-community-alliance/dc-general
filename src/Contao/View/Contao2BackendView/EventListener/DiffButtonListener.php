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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;

use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This opens the version comparison as a modal iframe for the "versions" operation button.
 *
 * Contao core injects a "versions" list operation for every table with "enableVersioning" via
 * Contao\CoreBundle\EventListener\DataContainer\DefaultOperationsListener, including a modern
 * "button_callback" closure that sets the "onclick" attribute opening the diff popup. dc-general's
 * legacy DCA-to-Command translation reads the static operation keys (href, icon, ...) but never
 * invokes that callback, so the button rendered without it - the icon was there but nothing opened.
 *
 * @api
 */
class DiffButtonListener
{
    /**
     * Handle the event.
     *
     * @param GetOperationButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetOperationButtonEvent $event)
    {
        if ('versions' !== $event->getKey()) {
            return;
        }

        $model = $event->getModel();
        if (!$model instanceof ModelInterface) {
            return;
        }

        $translator = System::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);

        $title = StringUtil::specialchars(
            \str_replace(
                "'",
                "\\'",
                $translator->trans(
                    'MSC.recordOfTable',
                    [$model->getId(), $model->getProviderName()],
                    'contao_default'
                )
            )
        );

        $onclick = 'onclick="Backend.openModalIframe({title:\'' . $title . '\', '
            . 'url:this.href+\'&popup=1&nb=1\'});return false"';

        $event->setAttributes(\trim($event->getAttributes() . ' ' . $onclick));
    }
}
