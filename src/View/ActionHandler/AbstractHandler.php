<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use LogicException;

/**
 * Abstract base class for handling dc-general action events.
 *
 * @deprecated This class is deprecated as it is an event listener with a changing state and will get removed.
 */
abstract class AbstractHandler
{
    /**
     * The event.
     *
     * @var ActionEvent|null
     */
    private ?ActionEvent $event = null;

    /**
     * Method to buffer the event and then process it.
     *
     * @param ActionEvent $event The event to process.
     *
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        $this->event = $event;
        $this->process();
        $this->event = null;
    }

    /**
     * Retrieve the environment.
     *
     * @return ActionEvent
     */
    protected function getEvent()
    {
        if (null === $this->event) {
            throw new LogicException('No event set.');
        }
        return $this->event;
    }

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     */
    protected function getEnvironment()
    {
        return $this->getEvent()->getEnvironment();
    }

    /**
     * Guard that the environment is prepared for models data definition.
     *
     * @param ModelIdInterface $modelId The model id.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException If data provider name of modelId and definition does not match.
     */
    protected function guardValidEnvironment(ModelIdInterface $modelId)
    {
        if ($this->getDataDefinition()->getName() !== $modelId->getDataProviderName()) {
            throw new DcGeneralRuntimeException(
                \sprintf(
                    'Not able to perform action. Environment is not prepared for model "%s"',
                    $modelId->getSerialized()
                )
            );
        }
    }

    /**
     * Guard that the data container is not in edit only mode.
     *
     * @param ModelIdInterface $modelId The model id.
     *
     * @return void
     *
     * @throws EditOnlyModeException If data container is in edit only mode.
     */
    protected function guardNotEditOnly(ModelIdInterface $modelId)
    {
        if ($this->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            throw new EditOnlyModeException($modelId->getDataProviderName());
        }
    }

    /**
     * Get response from edit action if we are in edit only mode.
     *
     * It returns true if the data definition is in edit only mode.
     *
     * @return bool
     */
    protected function isEditOnlyResponse()
    {
        if ($this->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            $this->callAction('edit');

            return true;
        }

        return false;
    }

    /**
     * Call a dc-general action (sub processing).
     *
     * @param string $actionName The action name.
     * @param array  $arguments  The optional action arguments.
     *
     * @return void
     */
    protected function callAction($actionName, $arguments = [])
    {
        $environment = $this->getEnvironment();
        // Keep the event as we might get called recursively.
        $keepEvent  = $this->event;
        $event      = new ActionEvent($environment, new Action($actionName, $arguments));
        $dispatcher = $environment->getEventDispatcher();
        if (null === $dispatcher) {
            throw new LogicException('No event dispatcher found in environment.');
        }
        $dispatcher->dispatch($event, DcGeneralEvents::ACTION);
        // Restore the event as we might get called recursively.
        $this->event = $keepEvent;

        $this->getEvent()->setResponse($event->getResponse());
    }

    /**
     * Handle the action.
     *
     * @return void
     */
    abstract public function process();

    private function getDataDefinition(): ContainerInterface
    {
        if (null === $definition = $this->getEnvironment()->getDataDefinition()) {
            throw new LogicException('No data definition found in environment.');
        }

        return $definition;
    }
}
