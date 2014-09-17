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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\DataProviderInformationInterface;

/**
 * This interface describes a collection of data provider information.
 *
 * @package DcGeneral\DataDefinition\Definition
 */
interface DataProviderDefinitionInterface
    extends DefinitionInterface,
    \IteratorAggregate,
    \Countable,
    \ArrayAccess
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
     *
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
