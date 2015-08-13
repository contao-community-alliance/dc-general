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
 * Default implementation of a sort definition.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
class DefaultSortElementInformation implements SortElementInformationInterface
{
	/**
	 * The names of the properties to be sortable.
	 *
	 * @var array
	 *
	 * @deprecated not in use anymore.
	 */
	protected $properties = array();

	/**
	 * The sorting flag to use by default.
	 *
	 * @var string
	 *
	 * @deprecated not in use anymore.
	 */
	protected $defaultFlag = SortElementInformationInterface::SORTING_FLAG_NONE;

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function getName()
	{
		return 'sort';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function setDefaultFlag($flag)
	{
		$this->defaultFlag = $flag;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function getDefaultFlag()
	{
		return $this->defaultFlag;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function addProperty($propertyName, $flag = 0)
	{
		$this->properties[$propertyName] = $flag;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function hasProperty($propertyName)
	{
		return isset($this->properties[$propertyName]);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function removeProperty($propertyName)
	{
		unset($this->properties[$propertyName]);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function getPropertyNames()
	{
		return array_keys($this->properties);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
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
