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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition;

/**
 * A generic data provider information.
 *
 * @package DcGeneral\DataDefinition
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
