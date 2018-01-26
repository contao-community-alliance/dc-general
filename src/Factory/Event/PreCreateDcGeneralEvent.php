<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Factory\Event;

use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactoryInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is emitted when a DcGeneral instance has been created.
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
