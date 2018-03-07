<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Class DefaultModel.
 *
 * Reference implementation of a dumb model.
 */
class DefaultModel extends AbstractModel
{
    /**
     * A list with all properties.
     *
     * @var array
     */
    protected $arrProperties = [];

    /**
     * The Id of this model.
     *
     * @var mixed
     */
    protected $mixID;

    /**
     * The name of the corresponding data provider.
     *
     * @var string
     */
    protected $strProviderName;

    /**
     * Copy this model, without the id.
     *
     * @return void
     */
    public function __clone()
    {
        $this->mixID = null;
    }

    /**
     * Get the id for this model.
     *
     * @return string The ID for this model.
     */
    public function getID()
    {
        return $this->mixID;
    }

    /**
     * Fetch the property with the given name from the model.
     *
     * This method returns null if an unknown property is retrieved.
     *
     * @param string $strPropertyName The property name to be retrieved.
     *
     * @return mixed The value of the given property.
     */
    public function getProperty($strPropertyName)
    {
        if ($strPropertyName == 'id') {
            return $this->getID();
        }

        if (array_key_exists($strPropertyName, $this->arrProperties)) {
            return $this->arrProperties[$strPropertyName];
        }

        return null;
    }

    /**
     * Fetch all properties from the model as an name => value array.
     *
     * @return array
     */
    public function getPropertiesAsArray()
    {
        $arrArray       = $this->arrProperties;
        $arrArray['id'] = $this->mixID;

        return $arrArray;
    }

    /**
     * Set the id for this object.
     *
     * NOTE: when the Id has been set once to a non null value, it can NOT be changed anymore.
     *
     * Normally this should only be called from inside of the implementing provider.
     *
     * @param mixed $mixID Could be a integer, string or anything else - depends on the provider implementation.
     *
     * @return void
     */
    public function setID($mixID)
    {
        if ($this->mixID == null) {
            $this->setIdRaw($mixID);
            $this->setMeta(static::IS_CHANGED, true);
        }
    }

    /**
     * Set the id for this object.
     *
     * This method is not interfaced and MUST only be used for initial values from the data provider.
     *
     * @param mixed $mixID Could be a integer, string or anything else - depends on the provider implementation.
     *
     * @return void
     */
    public function setIdRaw($mixID)
    {
        $this->mixID = $mixID;
    }

    /**
     * Update the property value in the model.
     *
     * This method is not interfaced and MUST only be used for initial values from the data provider.
     *
     * @param string $strPropertyName The property name to be set.
     * @param mixed  $varValue        The value to be set.
     *
     * @return void
     */
    public function setPropertyRaw($strPropertyName, $varValue)
    {
        $this->arrProperties[$strPropertyName] = $varValue;
    }

    /**
     * Update the property value in the model.
     *
     * @param string $strPropertyName The property name to be set.
     * @param mixed  $varValue        The value to be set.
     *
     * @return void
     */
    public function setProperty($strPropertyName, $varValue)
    {
        if ($varValue !== $this->getProperty($strPropertyName)) {
            $this->setMeta(static::IS_CHANGED, true);
            $this->setPropertyRaw($strPropertyName, $varValue);
        }
    }

    /**
     * Update all properties in the model.
     *
     * @param array $arrProperties The property values as name => value pairs.
     *
     * @return void
     */
    public function setPropertiesAsArray($arrProperties)
    {
        if (is_array($arrProperties)) {
            if (array_key_exists('id', $arrProperties)) {
                unset($arrProperties['id']);
            }

            foreach ($arrProperties as $strPropertyName => $varValue) {
                $this->setProperty($strPropertyName, $varValue);
            }
        }
    }

    /**
     * Check if this model have any properties.
     *
     * @return boolean true if any property has been stored, false otherwise.
     */
    public function hasProperties()
    {
        return count($this->arrProperties) != 0;
    }

    /**
     * Get an iterator for this model's property values.
     *
     * @return \IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->arrProperties);
    }

    /**
     * Sets the provider name in the model.
     *
     * NOTE: this is intended to be used by the data provider only and not by any user.
     * Changing this by hand may cause unexpected behaviour. So DO NOT USE IT.
     * For this reason, this method is not interfaced, as only the data provider knows how
     * to set itself to the model.
     *
     * @param string $strProviderName The name of the corresponding data provider.
     *
     * @return void
     */
    public function setProviderName($strProviderName)
    {
        $this->strProviderName = $strProviderName;
    }

    /**
     * Return the data provider name.
     *
     * @return string the name of the corresponding data provider.
     */
    public function getProviderName()
    {
        return $this->strProviderName;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When a property in the value bag has been marked as invalid.
     */
    public function readFromPropertyValueBag(PropertyValueBagInterface $valueBag)
    {
        foreach (array_keys($this->arrProperties) as $name) {
            if (!$valueBag->hasPropertyValue($name)) {
                continue;
            }

            if ($valueBag->isPropertyValueInvalid($name)) {
                throw new DcGeneralInvalidArgumentException('The value for property ' . $name . ' is invalid.');
            }

            $this->setProperty($name, $valueBag->getPropertyValue($name));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function writeToPropertyValueBag(PropertyValueBagInterface $valueBag)
    {
        foreach (array_keys($this->arrProperties) as $name) {
            if (!$valueBag->hasPropertyValue($name)) {
                continue;
            }

            $valueBag->setPropertyValue($name, $this->getProperty($name));
        }

        return $this;
    }
}
