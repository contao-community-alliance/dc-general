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

namespace ContaoCommunityAlliance\DcGeneral\Factory\Event;

use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactoryInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is emitted when a DcGeneral instance has been created.
 *
 * @package DcGeneral\Factory\Event
 */
class PreCreateDcGeneralEvent extends Event
{
    const NAME = 'dc-general.factory.pre-create-dc-general';

    /**
     * The factory calling.
     *
     * @var DcGeneralFactoryInterface
     */
    protected $factory;

    /**
     * Create a new instance.
     *
     * @param DcGeneralFactoryInterface $factory The factory.
     */
    public function __construct(DcGeneralFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Retrieve the factory.
     *
     * @return DcGeneralFactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }
}
