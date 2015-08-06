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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class GetParentHeaderEvent.
 *
 * This event gets emitted when the header section of a parent view is generated.
 */
class GetParentHeaderEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-parent-header';

    /**
     * The additional lines that shall be added to the header section.
     *
     * @var array
     */
    protected $additional;

    /**
     * Set the additional lines that shall be added to the header section.
     *
     * @param array $additional The lines to use as header.
     *
     * @return $this
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Get the additional lines that shall be added to the header section.
     *
     * @return array
     */
    public function getAdditional()
    {
        return $this->additional;
    }
}
