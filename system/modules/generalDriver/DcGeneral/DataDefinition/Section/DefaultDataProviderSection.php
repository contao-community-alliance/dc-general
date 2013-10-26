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

namespace DcGeneral\DataDefinition\Section;

use DcGeneral\DataDefinition\DataProviderInformation;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Interface DataProviderSectionInterface
 *
 * @package DcGeneral\DataDefinition\Section
 */
class DefaultDataProviderSection implements DataProviderSectionInterface
{
	/**
	 * @var DataProviderInformation[]
	 */
	protected $information = array();

	/**
	 * {@inheritdoc}
	 */
	public function addInformation($information)
	{
		if (!($information instanceof DataProviderInformation))
		{
			throw new DcGeneralInvalidArgumentException('Invalid value passed.');
		}

		$name = $information->getName();

		if ($this->hasInformation($name))
		{
			throw new DcGeneralInvalidArgumentException('Data provider name ' . $name . ' already registered.');
		}

		$this->information[$name] = $information;
	}

	/**
	 * @param $information
	 *
	 * @return string
	 *
	 * @throws DcGeneralInvalidArgumentException
	 *
	 * @internal
	 */
	protected function makeName($information)
	{
		if ($information instanceof DataProviderInformation)
		{
			$information = $information->getName();
		}

		if (!is_string($information))
		{
			throw new DcGeneralInvalidArgumentException('Invalid value passed.');
		}

		return $information;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeInformation($information)
	{
		unset($this->information[$information]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setInformation($name, $information)
	{
		$this->information[$name] = $information;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasInformation($information)
	{
		return array_key_exists($this->makeName($information), $this->information);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInformation($information)
	{
		return $this->information[$this->makeName($information)];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProviderNames()
	{
		return array_keys($this->information);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->information);
	}

	/**
	 * {@inheritdoc}
	 */
	public function count()
	{
		return count($this->information);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		return $this->hasInformation($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		return $this->getInformation($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		$this->setInformation($offset, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		$this->removeInformation($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	function __isset($name)
	{
		return $this->hasInformation($name);
	}

	/**
	 * {@inheritdoc}
	 */
	function __get($name)
	{
		return $this->getInformation($name);
	}

	/**
	 * {@inheritdoc}
	 */
	function __set($name, $value)
	{
		$this->setInformation($name, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	function __unset($name)
	{
		$this->removeInformation($name);
	}
}
