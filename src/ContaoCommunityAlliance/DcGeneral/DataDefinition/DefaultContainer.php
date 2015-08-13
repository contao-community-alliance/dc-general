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
 * @package DcGeneral\DataDefinition
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
    protected $definitions = array();

    /**
     * Create a new default container.
     *
     * @param string $name The name of the container.
     */
    public function __construct($name)
    {
        $this->name = (string)$name;
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
        $this->definitions = array();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinitions(array $definitions)
    {
        $this
            ->clearDefinitions()
            ->addDefinitions($definitions);

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
                'Definition ' . $definitionName . ' is not registered in the configuration ' . $this->getName() .'.'
            );
        }

        return $this->definitions[$definitionName];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitionNames()
    {
        return array_keys($this->definitions);
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
    public function setBasicDefinition(BasicDefinitionInterface $definition)
    {
        return $this->setDefinition(BasicDefinitionInterface::NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicDefinition()
    {
        return $this->getDefinition(BasicDefinitionInterface::NAME);
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
    public function setPropertiesDefinition(PropertiesDefinitionInterface $definition)
    {
        return $this->setDefinition(PropertiesDefinitionInterface::NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertiesDefinition()
    {
        return $this->getDefinition(PropertiesDefinitionInterface::NAME);
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
    public function setPalettesDefinition(PalettesDefinitionInterface $definition)
    {
        return $this->setDefinition(PalettesDefinitionInterface::NAME, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getPalettesDefinition()
    {
        return $this->getDefinition(PalettesDefinitionInterface::NAME);
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
        return $this->getDefinition(DataProviderDefinitionInterface::NAME);
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
        return $this->getDefinition(ModelRelationshipDefinitionInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $definitions = array();
        foreach ($this->definitions as $name => $definition) {
            $bobaFett = clone $definition;

            $definitions[$name] = $bobaFett;
        }
        $this->definitions = $definitions;
    }
}
