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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class GetBreadcrumbEvent.
 *
 * This event gets issued when the backend listing bread crumb is generated.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetBreadcrumbEvent extends AbstractEnvironmentAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-breadcrumb';

    /**
     * The breadcrumb elements to be displayed in the backend.
     *
     * @var array
     */
    protected $elements;

    /**
     * Set the breadcrumb elements to be displayed in the backend.
     *
     * @param array $elements The elements.
     *
     * @return $this
     */
    public function setElements($elements)
    {
        $this->elements = $elements;

        return $this;
    }

    /**
     * Get the breadcrumb elements to be displayed in the backend.
     *
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }
}
