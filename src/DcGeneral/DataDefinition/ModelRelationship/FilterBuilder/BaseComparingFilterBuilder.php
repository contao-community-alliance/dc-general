<?php
/**
 * PHP version 5
 * @package    DcGeneral
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Handy helper class to generate and manipulate AND filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship\FilterBuilder
 */
class BaseComparingFilterBuilder
	extends BaseFilterBuilder
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
	 * Create a new instance.
	 *
	 * @param string $property The property name to be compared.
	 *
	 * @param mixed  $value    The value to be compared against.
	 *
	 * @param bool   $isRemote Flag determining if the passed value is a remote property name (only valid if filter is
	 *                         for parent child relationship and not for root elements).
	 */
	public function __construct($property, $value, $isRemote = false)
	{
		$this->property = $property;
		$this->value    = $value;
		$this->isRemote = $isRemote;
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
		if (isset($array['remote']))
		{
			$value  = $array['remote'];
			$remote = true;
		}
		else
		{
			$value  = $array['value'];
			$remote = false;
		}

		if (isset($array['property']))
		{
			$property = $array['property'];
		}
		elseif (isset($array['local']))
		{
			$property = $array['local'];
		}

		if (!(isset($value) && isset($property)))
		{
			throw new DcGeneralInvalidArgumentException('Invalid filter array provided.');
		}

		return new static($property, $value, $remote);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get()
	{
		return array(
			'operation' => $this->operation,
			($this->getBuilder()->isRootFilter() ? 'property' : 'local')  => $this->property,
			($this->isRemote ? 'remote' : 'value') => $this->value
		);
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
