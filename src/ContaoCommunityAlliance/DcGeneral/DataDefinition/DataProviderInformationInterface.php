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
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition;

/**
 * A generic data provider information.
 */
interface DataProviderInformationInterface
{
    /**
     * Set the name of the data provider.
     *
     * @param string $name The name.
     *
     * @return DataProviderInformationInterface
     */
    public function setName($name);

    /**
     * Retrieve the name of the data provider.
     *
     * @return string
     */
    public function getName();

    /**
     * Set if versioning is enabled for this data provider or not.
     *
     * @param bool $versioningEnabled The flag.
     *
     * @return DataProviderInformationInterface
     */
    public function setVersioningEnabled($versioningEnabled);

    /**
     * Check if versioning is enabled for this data provider or not.
     *
     * @return bool
     */
    public function isVersioningEnabled();
}
