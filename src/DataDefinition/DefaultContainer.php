<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation of a data definition container.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) We have to keep them as we implement the interfaces.
 */
class DefaultContainer implements ContainerInterface
{
    /**
     * The name of the container.
     *
     * @var string
     */
    protected $name;

    /**
     * The contained definition instances.
     *
     * @var DefinitionInterface[]
     */
    protected $definitions = [];

    /**
     * Create a new default container.
     *
     * @param string $name The name of the container.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDefinition($definitionName)
    {
        return isset($this->definitions[$definitionName]);
    }

    /**
     * {@inheritdoc}
     */
    public function clearDefinitions()
    {
        $this->definitions = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinitions(array $definitions)
    {
        $this->clearDefinitions()->addDefinitions($definitions);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When a passed definition does not implement the DefinitionInterface.
     */
    public function addDefinitions(array $definitions)
    {
        foreach ($definitions as $name => $definition) {
            if (!($definition instanceof DefinitionInterface)) {
                throw new DcGeneralInvalidArgumentException(
                    'Definition ' . $name . ' does not implement DefinitionInterface.'
                );
            }

            $this->setDefinition($name, $definition);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinition($definitionName, DefinitionInterface $definition)
    {
        $this->definitions[$definitionName] = $definition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeDefinition($definitionName)
    {
        unset($this->definitions[$definitionName]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException Is thrown when there is no definition with this name.
     */
    public function getDefinition($definitionName)
    {
        if (!$this->hasDefinition($definitionName)) {
            throw new DcGeneralInvalidArgumentException(
                'Definition ' . $definitionName . ' is not registered in the configuration ' . $this->getName() . '.'
            );
        }

        return $this->definitions[$definitionName];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitionNames()
    {
        return \array_keys($this->definitions);
    }

    /**
     * {@inheritdoc}
     */
    public function hasBasicDefinition()
    {
        return $this->hasDefinition(BasicDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setBasicDefinition(BasicDefinitionInterface $basicDefinition)
    {
        return $this->setDefinition(BasicDefinitionInterface::NAME, $basicDefinition);
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicDefinition()
    {
        $definition = $this->getDefinition(BasicDefinitionInterface::NAME);
        assert($definition instanceof BasicDefinitionInterface);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPropertiesDefinition()
    {
        return $this->hasDefinition(PropertiesDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setPropertiesDefinition(PropertiesDefinitionInterface $propertiesDefinition)
    {
        return $this->setDefinition(PropertiesDefinitionInterface::NAME, $propertiesDefinition);
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertiesDefinition()
    {
        $definition = $this->getDefinition(PropertiesDefinitionInterface::NAME);
        assert($definition instanceof PropertiesDefinitionInterface);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPalettesDefinition()
    {
        return $this->hasDefinition(PalettesDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setPalettesDefinition(PalettesDefinitionInterface $palettesDefinition)
    {
        return $this->setDefinition(PalettesDefinitionInterface::NAME, $palettesDefinition);
    }

    /**
     * {@inheritdoc}
     */
    public function getPalettesDefinition()
    {
        $definition = $this->getDefinition(PalettesDefinitionInterface::NAME);
        assert($definition instanceof PalettesDefinitionInterface);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDataProviderDefinition()
    {
        return $this->hasDefinition(DataProviderDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setDataProviderDefinition(DataProviderDefinitionInterface $definition)
    {
        return $this->setDefinition(DataProviderDefinitionInterface::NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataProviderDefinition()
    {
        $definition = $this->getDefinition(DataProviderDefinitionInterface::NAME);
        assert($definition instanceof DataProviderDefinitionInterface);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function hasModelRelationshipDefinition()
    {
        return $this->hasDefinition(ModelRelationshipDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setModelRelationshipDefinition(ModelRelationshipDefinitionInterface $definition)
    {
        return $this->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getModelRelationshipDefinition()
    {
        $definition = $this->getDefinition(ModelRelationshipDefinitionInterface::NAME);
        assert($definition instanceof ModelRelationshipDefinitionInterface);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $definitions = [];
        foreach ($this->definitions as $name => $definition) {
            $definitions[$name] = clone $definition;
        }
        $this->definitions = $definitions;
    }
}
