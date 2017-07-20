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

use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class ParentViewChildRecordEvent.
 *
 * This event gets emitted when a child record gets rendered in the parent view.
 */
class ParentViewChildRecordEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.parent-view-child-record';

    /**
     * The html code to use for the model.
     *
     * @var string
     */
    protected $html;

    /**
     * Set the html code to use as child record.
     *
     * @param string $html The html code.
     *
     * @return $this
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Retrieve the stored html code for the child record.
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }
}
