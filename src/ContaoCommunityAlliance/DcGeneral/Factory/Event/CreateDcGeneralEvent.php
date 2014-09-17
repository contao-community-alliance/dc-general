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

namespace ContaoCommunityAlliance\DcGeneral\Factory\Event;

use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is emitted when a DcGeneral instance has been created.
 *
 * @package DcGeneral\Factory\Event
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
