<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This handles the back button event in list views.
 */
class BackButtonListener
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * Handle the event.
     *
     * @param GetGlobalButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetGlobalButtonEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        if ('back_button' !== $event->getKey()) {
            return;
        }

        $environment   = $event->getEnvironment();
        $inputProvider = $environment->getInputProvider();

        if (!(\in_array($inputProvider->getParameter('act'), ['edit', 'create'])
              || (null !== $inputProvider->getParameter('pid')
                  || (null !== $inputProvider->getParameter('select'))))
        ) {
            $event->setHtml('');
            return;
        }

        if (('select' === $inputProvider->getParameter('act'))
            && ('models' !== $inputProvider->getParameter('select'))
        ) {
            return;
        }

        $event->setHref($this->getReferrerUrl($environment));
    }

    /**
     * Determine the correct referrer URL.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return mixed
     */
    private function getReferrerUrl(EnvironmentInterface $environment)
    {
        $parent = $environment->getParentDataDefinition();
        $event  = new GetReferrerEvent(
            true,
            (null !== $parent)
                ? $parent->getName()
                : $environment->getDataDefinition()->getName()
        );

        $environment->getEventDispatcher()->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $event);

        return $event->getReferrerUrl();
    }
}
