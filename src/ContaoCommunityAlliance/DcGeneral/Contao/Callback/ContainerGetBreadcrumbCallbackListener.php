<?php
/**
 * PHP version 5
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
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;

/**
 * Class ContainerGetBreadcrumbCallbackListener.
 *
 * Callback handler for breadcrumbs in backend.
 *
 * @package DcGeneral\Contao\Callback
 */
class ContainerGetBreadcrumbCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * Retrieve the arguments for the callback.
     *
     * @param GetBreadcrumbEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        return array(
            new DcCompat($event->getEnvironment())
        );
    }

    /**
     * Update the information in the event with the list of breadcrumb elements returned by the callback.
     *
     * @param GetBreadcrumbEvent $event The event being emitted.
     *
     * @param array              $value The breadcrumb elements returned by the callback.
     *
     * @return void
     */
    public function update($event, $value)
    {
        if (is_null($value))
        {
            return;
        }

        $event->setElements($value);
    }
}
