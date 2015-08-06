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
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Factory\Event;

use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is emitted when a DcGeneral instance has been created.
 */
class CreateDcGeneralEvent extends Event
{
    const NAME = 'dc-general.factory.create-dc-general';

    /**
     * The instance that has been created.
     *
     * @var DcGeneral
     */
    protected $dcGeneral;

    /**
     * Create a new instance.
     *
     * @param DcGeneral $dcGeneral The DcGeneral instance.
     */
    public function __construct(DcGeneral $dcGeneral)
    {
        $this->dcGeneral = $dcGeneral;
    }

    /**
     * Retrieve the DcGeneral instance.
     *
     * @return DcGeneral
     */
    public function getDcGeneral()
    {
        return $this->dcGeneral;
    }
}
