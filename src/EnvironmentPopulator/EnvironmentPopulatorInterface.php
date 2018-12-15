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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This interface describes a generic environment populator.
 *
 * It only consists of a single "populate" method which will create instances of objects and push them into the
 * environment.
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
