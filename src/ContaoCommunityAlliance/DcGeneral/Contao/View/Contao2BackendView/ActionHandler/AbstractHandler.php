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

use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

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
     * Handle the action.
     *
     * @return mixed
     */
    abstract public function process();
}
