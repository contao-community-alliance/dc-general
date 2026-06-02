<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
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
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Default implementation of a palette.
 *
 * @api
 */
class Palette implements PaletteInterface
{
    /**
     * The name of this palette.
     *
     * @var string
     */
    protected $name = '';

    /**
     * List of all legends in this palette.
     *
     * @var array<string, LegendInterface>
     */
    protected $legends = [];

    /**
     * The condition bound to this palette.
     *
     * @var PaletteConditionInterface|null
     */
    protected $condition;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
    public function getProperties(?ModelInterface $model = null, ?PropertyValueBag $input = null)
    {
        $properties = [[]];
        foreach ($this->legends as $legend) {
            $properties[] = $legend->getProperties($model, $input);
        }

        return \array_merge(...$properties);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getVisibleProperties(?ModelInterface $model = null, ?PropertyValueBag $input = null)
    {
        $properties = [];
        foreach ($this->getLegends() as $legend) {
            foreach ($legend->getProperties($model, $input) as $property) {
                if ($property->isVisible($model, $input, $legend)) {
                    $properties[] = $property;
                }
            }
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getEditableProperties(?ModelInterface $model = null, ?PropertyValueBag $input = null)
    {
        $properties = [];
        foreach ($this->getLegends() as $legend) {
            foreach ($legend->getProperties($model, $input) as $property) {
                if ($property->isEditable($model, $input, $legend)) {
                    $properties[] = $property;
                }
            }
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When the palette does not contain the desired property.
     */
    #[\Override]
    public function getProperty($propertyName)
    {
        foreach ($this->getLegends() as $legend) {
            if ($legend->hasProperty($propertyName)) {
                return $legend->getProperty($propertyName);
            }
        }

        throw new DcGeneralRuntimeException(
            \sprintf(
                'The palette %s does not contain a property named %s',
                $this->getName(),
                $propertyName
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function clearLegends()
    {
        $this->legends = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setLegends(array $legends)
    {
        $this->clearLegends()->addLegends($legends);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function addLegends(array $legends, ?LegendInterface $before = null)
    {
        foreach ($legends as $legend) {
            $this->addLegend($legend, $before);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasLegend($name)
    {
        foreach ($this->legends as $legend) {
            if ($name === $legend->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function containsLegend(LegendInterface $legend)
    {
        return isset($this->legends[\spl_object_hash($legend)]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When the legend passed as $before can not be found.
     */
    #[\Override]
    public function addLegend(LegendInterface $legend, ?LegendInterface $before = null)
    {
        $hash = \spl_object_hash($legend);

        if ($before) {
            $beforeHash = \spl_object_hash($before);

            if (!isset($this->legends[$beforeHash])) {
                throw new DcGeneralInvalidArgumentException(
                    \sprintf(
                        'Legend %s not contained in palette - can not add %s after it.',
                        $before->getName(),
                        $legend->getName()
                    )
                );
            }

            $hashes   = \array_keys($this->legends);
            $position = \array_search($beforeHash, $hashes);

            /** @var array<string, LegendInterface> $merged */
            $merged        = \array_merge(
                \array_slice($this->legends, 0, (int) $position),
                [$hash => $legend],
                \array_slice($this->legends, (int) $position)
            );
            $this->legends = $merged;

            $legend->setPalette($this);
            return $this;
        }

        $this->legends[$hash] = $legend;

        $legend->setPalette($this);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function removeLegend(LegendInterface $legend)
    {
        unset($this->legends[\spl_object_hash($legend)]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When the legend does not exist.
     */
    #[\Override]
    public function getLegend($name)
    {
        foreach ($this->legends as $legend) {
            if ($name === $legend->getName()) {
                return $legend;
            }
        }

        throw new DcGeneralRuntimeException('Legend "' . $name . '" does not exists');
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getLegends(): array
    {
        return \array_values($this->legends);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setCondition(?PaletteConditionInterface $condition = null)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __clone()
    {
        /** @var array<string, LegendInterface> $legends */
        $legends = [];
        foreach ($this->legends as $legend) {
            $bobaFett = clone $legend;

            $legends[\spl_object_hash($bobaFett)] = $bobaFett->setPalette($this);
        }
        $this->legends = $legends;

        if (null !== $this->condition) {
            $this->condition = clone $this->condition;
        }
    }
}
