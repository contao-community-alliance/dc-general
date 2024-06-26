<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use Symfony\Contracts\EventDispatcher\Event;

use function call_user_func_array;

/**
 * Class AbstractCallbackListener.
 *
 * Abstract base class for a callback listener.
 *
 * @template TEvent of Event
 */
abstract class AbstractCallbackListener
{
    /**
     * The callback to use.
     *
     * @var array|callable
     */
    protected $callback;

    /**
     * The data container name to limit this execution to.
     *
     * @var null|string
     */
    protected $dataContainerName;

    /**
     * Create a new instance of the listener.
     *
     * @param array|callable $callback     The callback to call when invoked.
     * @param array|null     $restrictions The restrictions for the callback.
     */
    public function __construct($callback, $restrictions = null)
    {
        $this->callback = $callback;

        if (\is_array($restrictions)) {
            call_user_func_array([$this, 'setRestrictions'], $restrictions);
        }
    }

    /**
     * Set the restrictions for this callback.
     *
     * @param null|string $dataContainerName The name of the data container to limit execution on.
     *
     * @return void
     */
    public function setRestrictions(?string $dataContainerName = null)
    {
        $this->dataContainerName = $dataContainerName;
    }

    /**
     * Check the restrictions against the information within the event and determine if the callback shall be executed.
     *
     * @param TEvent $event The Event for which the callback shall be invoked.
     *
     * @return bool
     */
    public function wantToExecute($event)
    {
        if (null === $this->dataContainerName) {
            return true;
        }
        if (!$event instanceof EnvironmentAwareInterface) {
            throw new \LogicException('Event is not an instance of ' . EnvironmentAwareInterface::class);
        }
        if (null === $definition = $event->getEnvironment()->getDataDefinition()) {
            return false;
        }

        return ($this->dataContainerName === $definition->getName());
    }

    /**
     * Retrieve the attached callback.
     *
     * @return array|callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Retrieve the arguments for the callback.
     *
     * @param TEvent $event The event being emitted.
     *
     * @return array
     */
    abstract public function getArgs($event);

    /**
     * Invoke the callback.
     *
     * @param TEvent $event The Event for which the callback shall be invoked.
     *
     * @return void
     */
    public function __invoke($event)
    {
        if ($this->getCallback() && $this->wantToExecute($event)) {
            Callbacks::callArgs($this->getCallback(), $this->getArgs($event));
        }
    }
}
