<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * Class ModelOptionsCallbackListener.
 *
 * Handle the options_callback for a model in edit view.
 *
 * @package DcGeneral\Contao\Callback
 */
class ModelOptionsCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param GetPropertyOptionsEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array(
            new DcCompat($event->getEnvironment(), $event->getModel())
        );
    }

    /**
     * Update the options list in the event.
     *
     * @param GetPropertyOptionsEvent $event The event being emitted.
     *
     * @param array                   $value The options array.
     *
     * @return void
     */
    public function update($event, $value)
    {
        if (is_null($value)) {
            return;
        }

        $event->setOptions($value);
        $event->stopPropagation();
    }
}
