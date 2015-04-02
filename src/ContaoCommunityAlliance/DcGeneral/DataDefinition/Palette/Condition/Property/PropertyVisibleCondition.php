<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
        $this->propertyName = (string) $propertyName;
    }

    /**
     * Set the property name.
     *
     * @param string $propertyName The property name.
     *
     * @return PropertyValueCondition
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = (string) $propertyName;
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

        if ($legend->getPalette()) {
            $property = $legend->getPalette()->getProperty($this->propertyName);
        } else {
            $property = $legend->getProperty($this->propertyName);
        }

        return $property->isVisible($model, $input, $legend);
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
