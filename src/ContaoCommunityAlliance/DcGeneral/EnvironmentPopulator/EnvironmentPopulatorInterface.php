<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This interface describes a generic environment populator.
 *
 * It only consists of a single "populate" method which will create instances of objects and push them into the
 * environment.
 *
 * @package DcGeneral\EnvironmentPopulator
 */
interface EnvironmentPopulatorInterface
{
    /**
     * Create all needed objects the populator knows to create and put them into the environment.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     */
    public function populate(EnvironmentInterface $environment);
}
