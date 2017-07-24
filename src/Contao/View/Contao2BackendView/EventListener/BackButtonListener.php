<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;

/**
 * This handles the back button event in list views.
 */
class BackButtonListener
{
    /**
     * The request mode if contao is in frontend or backend mode.
     *
     * @var string
     */
    private $requestMode = '';


    /**
     * BackButtonListener constructor.
     *
     * @param ResettableContainerInterface $container
     */
    public function __construct(ResettableContainerInterface $container)
    {
        $requestStack   = $container->get('request_stack');
        $currentRequest = $requestStack->getCurrentRequest();
        if (null === $currentRequest) {
            return;
        }

        $scopeMatcher = $container->get('contao.routing.scope_matcher');

        if ($scopeMatcher->isBackendRequest($currentRequest)) {
            $this->requestMode = 'BE';
        }

        if ($scopeMatcher->isFrontendRequest($currentRequest)) {
            $this->requestMode = 'FE';
        }
    }

    /**
     * Handle the event.
     *
     * @param GetGlobalButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetGlobalButtonEvent $event)
    {
        if ('BE' !== $this->requestMode) {
            return;
        }

        if ('back_button' !== $event->getKey()) {
            return;
        }

        $environment = $event->getEnvironment();
        if (!('select' === $environment->getInputProvider()->getParameter('act')
            || (null !== $environment->getParentDataDefinition()))
        ) {
            $event->setHtml('');
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
