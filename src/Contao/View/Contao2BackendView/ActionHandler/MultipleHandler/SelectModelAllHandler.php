<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\MultipleHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;

/**
 * Handler for select models.
 */
class SelectModelAllHandler
{
    use RequestScopeDeterminatorAwareTrait;
    use CallActionTrait;

    /**
     * SelectModelAllHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * Handle the event.
     *
     * @param ActionEvent $event The event.
     *
     * @return null
     */
    public function handleEvent(ActionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()
            || 'selectModelAll' !== $event->getAction()->getName()
        ) {
            return null;
        }

        if ($response = $this->process($event->getAction(), $event->getEnvironment())) {
            $event->setResponse($response);
        }
    }

    /**
     * Process the event.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    private function process(Action $action, EnvironmentInterface $environment)
    {
        $arguments           = $action->getArguments();
        $arguments['mode']   = $environment->getInputProvider()->getParameter('mode');
        $arguments['select'] = $environment->getInputProvider()->getParameter('select');

        return $this->callAction($environment, 'showAll', $arguments);
    }
}
