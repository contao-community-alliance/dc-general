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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;

/**
 * Condition checking that the value of a property is true.
 */
class PropertyTrueCondition extends AbstractWeightAwarePaletteCondition
{
    /**
     * The property name.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * Use strict compare mode.
     *
     * @var bool
     */
    protected $strict;

    /**
     * Create a new instance.
     *
     * @param string $propertyName The name of the property.
     *
     * @param bool   $strict       Flag if the comparison shall be strict (type safe).
     *
     * @param int    $weight       The weight of this condition to apply.
     */
    public function __construct($propertyName = '', $strict = false, $weight = 1)
    {
        $this->propertyName = (string)$propertyName;
        $this->strict       = (bool)$strict;
        $this->setWeight($weight);
    }

    /**
     * Set the property name.
     *
     * @param string $propertyName The property name.
     *
     * @return PropertyTrueCondition
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = (string)$propertyName;

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
     * Set the flag if the comparison shall be strict (type safe).
     *
     * @param boolean $strict The flag.
     *
     * @return PropertyTrueCondition
     */
    public function setStrict($strict)
    {
        $this->strict = (bool)$strict;

        return $this;
    }

    /**
     * Retrieve the flag if the comparison shall be strict (type safe).
     *
     * @return boolean
     */
    public function getStrict()
    {
        return $this->strict;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchCount(ModelInterface $model = null, PropertyValueBag $input = null)
    {
        if (!$this->propertyName)
        {
            return false;
        }

        if ($input && $input->hasPropertyValue($this->propertyName))
        {
            $value = $input->getPropertyValue($this->propertyName);
        }
        elseif ($model)
        {
            $value = $model->getProperty($this->propertyName);
        }
        else
        {
            return false;
        }

        return ($this->strict ? ($value === true) : $value) ? $this->getWeight() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
