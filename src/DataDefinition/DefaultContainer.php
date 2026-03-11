<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
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
 *
 * @api
 */
class DefaultContainer implements ContainerInterface
{
    /**
     * The name of the container.
     *
     * @var string
     */
    protected string $name;

    /**
     * The contained definition instances.
     *
     * @var DefinitionInterface[]
     */
    protected array $definitions = [];

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
    #[\Override]
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasDefinition($definitionName)
    {
        return isset($this->definitions[$definitionName]);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function clearDefinitions()
    {
        $this->definitions = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
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
    #[\Override]
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
    #[\Override]
    public function setDefinition($definitionName, DefinitionInterface $definition)
    {
        $this->definitions[$definitionName] = $definition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
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
    #[\Override]
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
    #[\Override]
    public function getDefinitionNames()
    {
        return \array_keys($this->definitions);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasBasicDefinition()
    {
        return $this->hasDefinition(BasicDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setBasicDefinition(BasicDefinitionInterface $basicDefinition)
    {
        return $this->setDefinition(BasicDefinitionInterface::NAME, $basicDefinition);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getBasicDefinition()
    {
        $definition = $this->getDefinition(BasicDefinitionInterface::NAME);
        assert($definition instanceof BasicDefinitionInterface);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasPropertiesDefinition()
    {
        return $this->hasDefinition(PropertiesDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setPropertiesDefinition(PropertiesDefinitionInterface $propertiesDefinition)
    {
        return $this->setDefinition(PropertiesDefinitionInterface::NAME, $propertiesDefinition);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getPropertiesDefinition()
    {
        $definition = $this->getDefinition(PropertiesDefinitionInterface::NAME);
        assert($definition instanceof PropertiesDefinitionInterface);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasPalettesDefinition()
    {
        return $this->hasDefinition(PalettesDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setPalettesDefinition(PalettesDefinitionInterface $palettesDefinition)
    {
        return $this->setDefinition(PalettesDefinitionInterface::NAME, $palettesDefinition);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getPalettesDefinition()
    {
        $definition = $this->getDefinition(PalettesDefinitionInterface::NAME);
        assert($definition instanceof PalettesDefinitionInterface);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasDataProviderDefinition()
    {
        return $this->hasDefinition(DataProviderDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDataProviderDefinition(DataProviderDefinitionInterface $definition)
    {
        return $this->setDefinition(DataProviderDefinitionInterface::NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getDataProviderDefinition()
    {
        $definition = $this->getDefinition(DataProviderDefinitionInterface::NAME);
        assert($definition instanceof DataProviderDefinitionInterface);

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasModelRelationshipDefinition()
    {
        return $this->hasDefinition(ModelRelationshipDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setModelRelationshipDefinition(ModelRelationshipDefinitionInterface $definition)
    {
        return $this->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
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
