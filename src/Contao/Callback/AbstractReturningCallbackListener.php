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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

/**
 * Class AbstractReturningCallbackListener.
 *
 * Abstract base class for callbacks that are returning a value.
 */
abstract class AbstractReturningCallbackListener extends AbstractCallbackListener
{
    /**
     * Update the values in the event with the value returned by the callback.
     *
     * @param \Symfony\Component\EventDispatcher\Event $event The event being emitted.
     *
     * @param mixed                                    $value The value returned by the callback.
     *
     * @return void
     */
    abstract public function update($event, $value);

    /**
     * {@inheritdoc}
     */
    public function __invoke($event)
    {
        if ($this->getCallback() && $this->wantToExecute($event)) {
            $this->update(
                $event,
                Callbacks::callArgs($this->getCallback(), $this->getArgs($event))
            );
        }
    }
}
