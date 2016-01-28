<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
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
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Abstract base class for handling dc-general action events.
 */
abstract class AbstractHandler
{
    /**
     * The event.
     *
     * @var ActionEvent
     */
    private $event = null;

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
        if ($this->getEnvironment()->getDataDefinition()->getName() !== $modelId->getDataProviderName()) {
            throw new DcGeneralRuntimeException(
                sprintf(
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
        if ($this->getEnvironment()->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
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
        if ($this->getEnvironment()->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            $event = new ActionEvent($this->getEnvironment(), new Action('edit'));
            $this->getEnvironment()->getEventDispatcher()->dispatch(DcGeneralEvents::ACTION, $event);
            $this->getEvent()->setResponse($event->getResponse());

            return true;
        }

        return false;
    }

    /**
     * Handle the action.
     *
     * @return void
     */
     abstract public function process();
}
