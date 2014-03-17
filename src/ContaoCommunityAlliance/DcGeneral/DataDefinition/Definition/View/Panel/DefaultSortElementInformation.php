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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Class DefaultSortElementInformation.
 *
 * Default implementation of a sort definition on properties.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
class DefaultSortElementInformation implements SortElementInformationInterface
{
	/**
	 * The names of the properties to be sortable.
	 *
	 * @var array
	 */
	protected $properties = array();

	/**
	 * The sorting flag to use by default.
	 *
	 * @var string
	 */
	protected $defaultFlag = SortElementInformationInterface::SORTING_FLAG_NONE;

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'sort';
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultFlag($flag)
	{
		$this->defaultFlag = $flag;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultFlag()
	{
		return $this->defaultFlag;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProperty($propertyName, $flag = 0)
	{
		$this->properties[$propertyName] = $flag;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasProperty($propertyName)
	{
		return isset($this->properties[$propertyName]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeProperty($propertyName)
	{
		unset($this->properties[$propertyName]);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyNames()
	{
		return array_keys($this->properties);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyFlag($propertyName)
	{
		$flag = $this->getDefaultFlag();

		if ($this->hasProperty($propertyName) && $this->properties[$propertyName])
		{
			$flag = $this->properties[$propertyName];
		}

		return $flag;
	}
}
