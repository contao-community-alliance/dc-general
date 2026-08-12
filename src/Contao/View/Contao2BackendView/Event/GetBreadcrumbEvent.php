<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
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
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class GetBreadcrumbEvent.
 *
 * This event gets issued when the backend listing bread crumb is generated.
 *
 * @api
 */
class GetBreadcrumbEvent extends AbstractEnvironmentAwareEvent
{
    public const string NAME = 'dc-general.view.contao2backend.get-breadcrumb';

    /**
     * The breadcrumb elements to be displayed in the backend.
     *
     * @var array
     */
    protected array $elements = [];

    /**
     * Links to sibling views, shown at the trailing end of the breadcrumb.
     *
     * Where the elements describe the way into the current view, these lead sideways out of it -
     * to the neighbouring views of the same record. Kept apart from the elements so that a
     * template can place and style them on their own.
     *
     * @var array
     */
    protected array $shortcuts = [];

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

    /**
     * Set the shortcuts to be displayed at the trailing end of the breadcrumb.
     *
     * @param array $shortcuts The shortcuts.
     *
     * @return $this
     */
    public function setShortcuts($shortcuts)
    {
        $this->shortcuts = $shortcuts;

        return $this;
    }

    /**
     * Get the shortcuts to be displayed at the trailing end of the breadcrumb.
     *
     * @return array
     */
    public function getShortcuts()
    {
        return $this->shortcuts;
    }
}
