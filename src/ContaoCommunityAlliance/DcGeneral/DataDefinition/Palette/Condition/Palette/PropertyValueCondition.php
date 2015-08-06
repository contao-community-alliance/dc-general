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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;

/**
 * Condition checking that the value of a property is the same as a passed value.
 */
class PropertyValueCondition extends AbstractWeightAwarePaletteCondition
{
    /**
     * The property name.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * The expected property value.
     *
     * @var mixed
     */
    protected $propertyValue;

    /**
     * Use strict compare mode.
     *
     * @var bool
     */
    protected $strict;

    /**
     * Create a new instance.
     *
     * @param string $propertyName  The name of the property.
     *
     * @param mixed  $propertyValue The value of the property to match.
     *
     * @param bool   $strict        Flag if the comparison shall be strict (type safe).
     *
     * @param int    $weight        The weight of this condition to apply.
     */
    public function __construct($propertyName = '', $propertyValue = null, $strict = false, $weight = 1)
    {
        $this->propertyName  = (string) $propertyName;
        $this->propertyValue = $propertyValue;
        $this->strict        = (bool) $strict;
        $this->setWeight($weight);
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
     * Set the property value to match.
     *
     * @param mixed $propertyValue The value.
     *
     * @return PropertyValueCondition
     */
    public function setPropertyValue($propertyValue)
    {
        $this->propertyValue = $propertyValue;
        return $this;
    }

    /**
     * Retrieve the property value to match.
     *
     * @return mixed
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }

    /**
     * Set the flag if the comparison shall be strict (type safe).
     *
     * @param boolean $strict The flag.
     *
     * @return PropertyValueCondition
     */
    public function setStrict($strict)
    {
        $this->strict = (bool) $strict;
        return $this;
    }

    /**
     * Retrieve the flag if the comparison shall be strict (type safe).
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
        if (!$this->propertyName) {
            return false;
        }

        if ($input && $input->hasPropertyValue($this->propertyName)) {
            $value = $input->getPropertyValue($this->propertyName);
        } elseif ($model) {
            $value = $model->getProperty($this->propertyName);
        } else {
            return false;
        }

        return ($this->strict ? ($value === $this->propertyValue) : ($value == $this->propertyValue))
            ? $this->getWeight()
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
