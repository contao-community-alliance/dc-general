<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditMaskSubHeadlineEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * This class handles events to handle sub-headline at input mask.
 */
class CreateSubHeadlineListener
{
    /**
     * Handle the edit mask sub-headline event.
     *
     * @param GetEditMaskSubHeadlineEvent $event
     *
     * @return void
     */
    public function __invoke(GetEditMaskSubHeadlineEvent $event): void
    {
        if (null !== $event->getHeadline()) {
            return;
        }

        $status = $event->getModel()->getId() ? 'editRecord' : 'newRecord';
        $this->createSubHeadline($status, $event);
    }

    /**
     * Create the sub-headline at input mask.
     *
     * @param string                      $status The status.
     * @param GetEditMaskSubHeadlineEvent $event  The event.
     *
     * @return void
     */
    private function createSubHeadline(string $status, GetEditMaskSubHeadlineEvent $event): void
    {
        $environment = $event->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $definitionName = $definition->getName();

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $headline = $translator->translate($status, $definitionName, ['%id%' => $event->getModel()->getId()]);

        if ($status !== $headline) {
            $subHeadline = $headline;
        } else {
            $subHeadline = $translator->translate($status, 'dc-general', ['%id%' => $event->getModel()->getId()]);
        }

        $event->setHeadline($subHeadline);
    }
}
