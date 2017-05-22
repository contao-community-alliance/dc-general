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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;

/**
 * Class ModelLabelCallbackListener.
 *
 * Handle the label_callbacks.
 */
class ModelLabelCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param ModelToLabelEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array(
            $event->getModel()->getPropertiesAsArray(),
            $event->getLabel(),
            new DcCompat($event->getEnvironment(), $event->getModel()),
            $event->getArgs()
        );
    }

    /**
     * Set the value in the event.
     *
     * @param ModelToLabelEvent $event The event being emitted.
     *
     * @param string            $value The label text to use.
     *
     * @return void
     */
    public function update($event, $value)
    {
        if ($value === null) {
            return;
        }

        // HACK: we need to escape all % chars but preserve the %s and the like.
        $value = str_replace('%', '%%', $value);
        $value = preg_replace(
            '#%(%([0-9]+\$)?(\'.|0| )?-?([0-9]+)?(.[0-9]+)?(b|c|d|e|E|f|F|g|G|o|s|u|x|X))#',
            '\\1',
            $value
        );

        $event->setLabel($value);
    }
}
