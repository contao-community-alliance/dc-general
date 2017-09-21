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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class GetBreadcrumbEvent.
 *
 * This event gets issued when the backend listing bread crumb is generated.
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
