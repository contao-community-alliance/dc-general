<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Default implementation of a legend.
 */
class Legend implements LegendInterface
{
    /**
     * The palette this legend belongs to.
     *
     * @var PaletteInterface|null
     */
    protected $palette = null;

    /**
     * The name of this legend.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Determinator if this legend is initially expanded.
     *
     * @var bool
     */
    protected $initiallyVisible = true;

    /**
     * The properties in this legend.
     *
     * @var PropertyInterface[]|array
     */
    protected $properties = array();

    /**
     * Create a new instance.
     *
     * @param string $name The name of the legend.
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setPalette(PaletteInterface $palette = null)
    {
        if ($this->palette) {
            $this->palette->removeLegend($this);
        }

        $this->palette = $palette;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPalette()
    {
        return $this->palette;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
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
    public function setInitialVisibility($value)
    {
        $this->initiallyVisible = (bool) $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialVisible()
    {
        return $this->initiallyVisible;
    }

    /**
     * {@inheritdoc}
     */
    public function clearProperties()
    {
        $this->properties = array();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperties(array $properties)
    {
        $this->clearProperties();
        $this->addProperties($properties);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addProperties(array $properties, PropertyInterface $before = null)
    {
        foreach ($properties as $property) {
            $this->addProperty($property, $before);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When the property passed as $before can not be found.
     */
    public function addProperty(PropertyInterface $property, PropertyInterface $before = null)
    {
        $hash = spl_object_hash($property);

        if ($before) {
            $beforeHash = spl_object_hash($before);

            if (isset($this->properties[$beforeHash])) {
                $hashes   = array_keys($this->properties);
                $position = array_search($beforeHash, $hashes);

                $this->properties = array_merge(
                    array_slice($this->properties, 0, $position),
                    array($hash => $property),
                    array_slice($this->properties, $position)
                );
            } else {
                throw new DcGeneralInvalidArgumentException(
                    sprintf(
                        'Property %s not contained in legend - can not add %s after it.',
                        $before->getName(),
                        $property->getName()
                    )
                );
            }
        } else {
            $this->properties[$hash] = $property;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeProperty(PropertyInterface $property)
    {
        $hash = spl_object_hash($property);
        unset($this->properties[$hash]);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties(ModelInterface $model = null, PropertyValueBag $input = null)
    {
        if ($model || $input) {
            $selectedProperties = array();

            foreach ($this->properties as $property) {
                $condition = $property->getVisibleCondition();

                if (!$condition || $condition->match($model, $input, $property, $this)) {
                    $selectedProperties[] = $property;
                }
            }

            return $selectedProperties;
        }

        return array_values($this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function hasProperty($propertyName)
    {
        foreach ($this->properties as $property) {
            if ($property->getName() == $propertyName) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When the legend does not contain the desired property.
     */
    public function getProperty($propertyName)
    {
        foreach ($this->properties as $property) {
            if ($property->getName() == $propertyName) {
                return $property;
            }
        }

        throw new DcGeneralRuntimeException(
            sprintf(
                'The legend %s does not contain a property named %s',
                $this->getName(),
                $propertyName
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->palette = null;

        $properties = array();
        foreach ($this->properties as $property) {
            $bobaFett = clone $property;

            $properties[spl_object_hash($bobaFett)] = $bobaFett;
        }
        $this->properties = $properties;
    }
}
