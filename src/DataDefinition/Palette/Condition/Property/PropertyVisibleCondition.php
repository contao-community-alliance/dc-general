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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * Condition checking that a property is visible.
 */
class PropertyVisibleCondition implements PropertyConditionInterface
{
    /**
     * The property name.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * Create a new instance.
     *
     * @param string $propertyName The name of the property.
     */
    public function __construct($propertyName = '')
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Set the property name.
     *
     * @param string $propertyName The property name.
     *
     * @return PropertyVisibleCondition
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
        return $this;
    }

    /**
     * Retrieve the property name.
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * {@inheritdoc}
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        if (!$legend) {
            return false;
        }

        if (null !== ($palette = $legend->getPalette())) {
            return $palette->getProperty($this->propertyName)->isVisible($model, $input, $legend);
        }

        return $legend->getProperty($this->propertyName)->isVisible($model, $input, $legend);
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
