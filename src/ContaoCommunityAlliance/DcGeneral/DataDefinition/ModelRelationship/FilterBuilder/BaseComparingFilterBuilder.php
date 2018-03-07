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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Handy helper class to generate and manipulate AND filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
 */
class BaseComparingFilterBuilder extends BaseFilterBuilder
{
    /**
     * The operation string.
     *
     * @var string
     */
    protected $operation;

    /**
     * The property to be checked.
     *
     * @var string
     */
    protected $property;

    /**
     * The property to be checked.
     *
     * @var string
     */
    protected $remoteProperty;

    /**
     * The value to compare against.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Flag determining if the passed value is a remote property name or not.
     *
     * @var bool
     */
    protected $isRemote;

    /**
     * Flag determining if the remote value is a property or literal value.
     *
     * @var bool
     */
    protected $isRemoteProp;

    /**
     * Create a new instance.
     *
     * @param string $property     The property name to be compared.
     * @param mixed  $value        The value to be compared against.
     * @param bool   $isRemote     Flag determining if the passed value is a remote property name (only valid if filter
     *                             is for parent child relationship and not for root elements).
     * @param bool   $isRemoteProp Flag determining if the passed value is a property or literal value (only valid when
     *                             $isRemote is true).
     */
    public function __construct($property, $value, $isRemote = false, $isRemoteProp = true)
    {
        $this
            ->setIsRemote($isRemote)
            ->setProperty($property)
            ->setValue($value);
        if ($this->isRemote()) {
            $this->setIsRemoteProperty($isRemoteProp);
        }
    }

    /**
     * Initialize an instance with the values from the given array.
     *
     * @param array $array The initialization array.
     *
     * @return mixed
     *
     * @throws DcGeneralInvalidArgumentException When an invalid array has been passed.
     */
    public static function fromArray($array)
    {
        $isRemote     = isset($array['remote']) || isset($array['remote_value']);
        $isRemoteProp = $isRemote && !isset($array['remote_value']);

        if ($isRemote) {
            if ($isRemoteProp) {
                $value = $array['remote'];
            } else {
                $value = $array['remote_value'];
            }
            $property = $array['local'];
        } else {
            $value    = $array['value'];
            $property = $array['property'];
        }

        if (!(isset($value) && isset($property))) {
            throw new DcGeneralInvalidArgumentException('Invalid filter array provided  ' . var_export($array, true));
        }

        return new static($property, $value, $isRemote, $isRemoteProp);
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $result = ['operation' => $this->operation,];

        if ($this->isRemote()) {
            $result['local'] = $this->getProperty();
            if ($this->isRemoteProperty()) {
                $result['remote'] = $this->value;
            } else {
                $result['remote_value'] = $this->value;
            }
        } else {
            $result['value']    = $this->value;
            $result['property'] = $this->getProperty();
        }

        return $result;
    }

    /**
     * Set the flag if this filter is for remote usage or not.
     *
     * @param boolean $isRemote The new flag.
     *
     * @return BaseComparingFilterBuilder
     */
    public function setIsRemote($isRemote)
    {
        $this->isRemote = $isRemote;

        return $this;
    }

    /**
     * Determine if this filter is for remote filtering or not.
     *
     * @return boolean
     */
    public function isRemote()
    {
        return $this->isRemote;
    }

    /**
     * Determine if the value is a property or literal value (Only valid if isRemote() == true).
     *
     * @return boolean
     *
     * @throws DcGeneralRuntimeException When the filter is not flagged as remote.
     */
    public function isRemoteProperty()
    {
        if (!$this->isRemote()) {
            throw new DcGeneralRuntimeException('Property value is not flagged for remote usage.');
        }

        return $this->isRemoteProp;
    }

    /**
     * Set the flag that this filters value is a remote property.
     *
     * @param bool $isRemoteProp True when the value is to be credited as property name, false if it is a literal value.
     *
     * @return BaseComparingFilterBuilder
     *
     * @throws DcGeneralRuntimeException When the filter is not flagged as remote.
     */
    public function setIsRemoteProperty($isRemoteProp)
    {
        if (!$this->isRemote()) {
            throw new DcGeneralRuntimeException('Property value is not flagged for remote usage.');
        }

        $this->isRemoteProp = $isRemoteProp;

        return $this;
    }

    /**
     * Set the property name.
     *
     * @param string $property The property name.
     *
     * @return BaseComparingFilterBuilder
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Retrieve the property name.
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set the value to filter for.
     *
     * @param mixed $value The value.
     *
     * @return BaseComparingFilterBuilder
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Retrieve the value to filter for.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
