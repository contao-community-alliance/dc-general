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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\MultipleHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
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
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
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
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        if (
            !$this->getScopeDeterminator()->currentScopeIsBackend()
            || ('selectModelAll' !== $event->getAction()->getName())
        ) {
            return;
        }

        if ('' !== ($response = $this->process($event->getAction(), $event->getEnvironment()))) {
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
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        return $this->callAction(
            $environment,
            'showAll',
            \array_merge(
                $action->getArguments(),
                [
                    'mode' => $inputProvider->getParameter('mode'),
                    'select' => $inputProvider->getParameter('select')
                ]
            )
        ) ?? '';
    }
}
