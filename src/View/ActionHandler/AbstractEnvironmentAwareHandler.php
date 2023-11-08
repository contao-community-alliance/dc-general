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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;

/**
 * The AbstractEnvironmentAwareHandler is designed for action handlers which can also be used in a nonevent context.
 *
 * It provides a setEnvironment method which has to be used to initialize the environment instead.
 *
 * @deprecated This class is deprecated as it is an event listener with a changing state and will get removed.
 *
 * @psalm-suppress DeprecatedClass
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
