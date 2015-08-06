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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;

/**
 * This interface is the base of a data definition.
 *
 * Within this interface, all the information about a data definition is to be found.
 *
 * Most commonly used definitions have their getter and setter defined in this interface, for those definitions that
 * are not so common, please use the generic getter and setter using names.
 */
interface ContainerInterface
{
    /**
     * Return the name of the container.
     *
     * @return string
     */
    public function getName();

    /**
     * Check if this container has a definition.
     *
     * @param string $definitionName The name of the definition to check for.
     *
     * @return bool
     */
    public function hasDefinition($definitionName);

    /**
     * Clear all definitions from this container.
     *
     * @return ContainerInterface
     */
    public function clearDefinitions();

    /**
     * Set the definitions of this container.
     *
     * @param array|DefinitionInterface[] $definitions The definitons.
     *
     * @return ContainerInterface
     */
    public function setDefinitions(array $definitions);

    /**
     * Add multiple definitions to this container.
     *
     * @param array|DefinitionInterface[] $definitions The definitons.
     *
     * @return ContainerInterface
     */
    public function addDefinitions(array $definitions);

    /**
     * Set a definitions of this container.
     *
     * @param string              $definitionName The name of the definition.
     *
     * @param DefinitionInterface $definition     The definition.
     *
     * @return ContainerInterface
     */
    public function setDefinition($definitionName, DefinitionInterface $definition);

    /**
     * Remove a definitions from this container.
     *
     * @param string $definitionName The name of the definition.
     *
     * @return ContainerInterface
     */
    public function removeDefinition($definitionName);

    /**
     * Get a definitions of this container.
     *
     * @param string $definitionName The name of the definition.
     *
     * @return DefinitionInterface
     */
    public function getDefinition($definitionName);

    /**
     * Get a list of all definition names in this container.
     *
     * @return array
     */
    public function getDefinitionNames();

    /**
     * Convenience method to check if a basic definition is contained.
     *
     * @return BasicDefinitionInterface
     */
    public function hasBasicDefinition();

    /**
     * Convenience method to set the basic definition.
     *
     * @param BasicDefinitionInterface $basicDefinition The basic definition to use.
     *
     * @return ContainerInterface
     */
    public function setBasicDefinition(BasicDefinitionInterface $basicDefinition);

    /**
     * Convenience method to retrieve the basic definition.
     *
     * @return BasicDefinitionInterface
     */
    public function getBasicDefinition();

    /**
     * Convenience method to check if there has been a properties definition defined.
     *
     * @return bool
     */
    public function hasPropertiesDefinition();

    /**
     * Convenience method to set the properties definition to use.
     *
     * @param PropertiesDefinitionInterface $propertiesDefinition The properties definition to use.
     *
     * @return ContainerInterface
     */
    public function setPropertiesDefinition(PropertiesDefinitionInterface $propertiesDefinition);

    /**
     * Convenience method to retrieve the properties definition to use.
     *
     * @return PropertiesDefinitionInterface
     */
    public function getPropertiesDefinition();

    /**
     * Convenience method to check if there has been a palettes definition defined.
     *
     * @return bool
     */
    public function hasPalettesDefinition();

    /**
     * Convenience method to set the palettes definition to use.
     *
     * @param PalettesDefinitionInterface $palettesDefinition The palettes definition to use.
     *
     * @return ContainerInterface
     */
    public function setPalettesDefinition(PalettesDefinitionInterface $palettesDefinition);

    /**
     * Convenience method to retrieve the palettes definition to use.
     *
     * @return PalettesDefinitionInterface
     */
    public function getPalettesDefinition();

    /**
     * Convenience method to check if a data provider definition is contained.
     *
     * @return bool
     */
    public function hasDataProviderDefinition();

    /**
     * Convenience method to set the data provider definition.
     *
     * @param DataProviderDefinitionInterface $definition The data provider definition to use.
     *
     * @return ContainerInterface
     */
    public function setDataProviderDefinition(DataProviderDefinitionInterface $definition);

    /**
     * Convenience method to retrieve the data provider definition.
     *
     * @return DataProviderDefinitionInterface
     */
    public function getDataProviderDefinition();

    /**
     * Convenience method to check if a data provider definition is contained.
     *
     * @return bool
     */
    public function hasModelRelationshipDefinition();

    /**
     * Convenience method to set the data provider definition.
     *
     * @param ModelRelationshipDefinitionInterface $definition The model relationship definition to use.
     *
     * @return ContainerInterface
     */
    public function setModelRelationshipDefinition(ModelRelationshipDefinitionInterface $definition);

    /**
     * Convenience method to retrieve the data provider definition.
     *
     * @return ModelRelationshipDefinitionInterface
     */
    public function getModelRelationshipDefinition();
}
