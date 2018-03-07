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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\DataProviderInformationInterface;

/**
 * This interface describes a collection of data provider information.
 */
interface DataProviderDefinitionInterface extends DefinitionInterface, \IteratorAggregate, \Countable, \ArrayAccess
{
    const NAME = 'dataProvider';

    /**
     * Add a data provider information to the definition.
     *
     * @param DataProviderInformationInterface $information The data provider instance to add.
     *
     * @return DataProviderDefinitionInterface
     */
    public function addInformation($information);

    /**
     * Remove the data provider information with the given name.
     *
     * @param DataProviderInformationInterface|string $information The information or name of a data provider.
     *
     * @return DataProviderDefinitionInterface
     */
    public function removeInformation($information);

    /**
     * Forcefully overwrite a stored data provider with another one.
     *
     * @param string                           $name        The name of a data provider to overwrite.
     * @param DataProviderInformationInterface $information The information of the new data provider.
     *
     * @return DataProviderDefinitionInterface
     */
    public function setInformation($name, $information);

    /**
     * Check if there exists a definition of a data provider with the given name.
     *
     * @param DataProviderInformationInterface|string $information The information or name of a data provider.
     *
     * @return bool
     */
    public function hasInformation($information);

    /**
     * Retrieve the data provider information with the given name.
     *
     * @param string $information The name of a data provider.
     *
     * @return DataProviderInformationInterface
     */
    public function getInformation($information);

    /**
     * Retrieve the names of all registered providers.
     *
     * @return string[]
     */
    public function getProviderNames();
}
