<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Handler class for handling the show events.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
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
     * Handle the action.
     *
     * @return mixed
     */
    abstract public function process();
}
