<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;

/**
 * The AbstractEnvironmentAwareHandler is designed for action handlers which can also be used in a non event context.
 *
 * It provides a setEnvironment method which has to be used to initialize the environment instead.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
 */
abstract class AbstractEnvironmentAwareHandler extends AbstractHandler implements EnvironmentAwareInterface
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set the environment. This is required for using handler for a non event mode.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return $this
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handleEvent(ActionEvent $event)
    {
        $this->setEnvironment($event->getEnvironment());
        parent::handleEvent($event);
    }
}
