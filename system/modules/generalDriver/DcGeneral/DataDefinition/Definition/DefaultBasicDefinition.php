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

namespace DcGeneral\DataDefinition\Definition;

/**
 * Interface DataProviderDefinitionInterface
 *
 * @package DcGeneral\DataDefinition\Definition
 */
class DefaultBasicDefinition implements BasicDefinitionInterface
{
	/**
	 * The mode.
	 *
	 * @var int
	 */
	protected $mode;

	/**
	 * @var string
	 */
	protected $rootProviderName;

	/**
	 * @var string
	 */
	protected $parentProviderName;

	/**
	 * @var string
	 */
	protected $providerName;

	/**
	 * @var array
	 */
	protected $additionalFilter;

	/**
	 * @var bool
	 */
	protected $isClosed = false;

	/**
	 * @var bool
	 */
	protected $isEditable = true;

	/**
	 * @var bool
	 */
	protected $isDeletable  = true;

	/**
	 * @var bool
	 */
	protected $isCreatable = true;

	/**
	 * @var bool
	 */
	protected $switchToEditEnabled;

	/**
	 * {@inheritdoc}
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRootDataProvider($providerName)
	{
		$this->rootProviderName = $providerName;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootDataProvider()
	{
		return $this->rootProviderName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setParentDataProvider($providerName)
	{
		$this->parentProviderName = $providerName;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParentDataProvider()
	{
		return $this->parentProviderName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDataProvider($providerName)
	{
		$this->providerName = $providerName;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDataProvider()
	{
		return $this->providerName;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setAdditionalFilter($dataProvider, $filter)
	{
		$this->additionalFilter[$dataProvider] = $filter;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasAdditionalFilter($dataProvider = null)
	{
		if ($dataProvider === null)
		{
			$dataProvider = $this->getDataProvider();
		}

		return isset($this->additionalFilter[$dataProvider]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAdditionalFilter($dataProvider = null)
	{
		if ($dataProvider === null)
		{
			$dataProvider = $this->getDataProvider();
		}

		return $this->additionalFilter[$dataProvider];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setClosed($value)
	{
		$this->isClosed = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isClosed()
	{
		return $this->isClosed;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEditable($value)
	{
		$this->isEditable = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEditable()
	{
		return $this->isEditable;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDeletable($value)
	{
		$this->isDeletable = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isDeletable()
	{
		return $this->isDeletable;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCreatable($value)
	{
		$this->isCreatable = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isCreatable()
	{
		return $this->isCreatable;
	}


	/**
	 * {@inheritdoc}
	 */
	public function setSwitchToEditEnabled($switchToEditEnabled)
	{
		$this->switchToEditEnabled = $switchToEditEnabled;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSwitchToEditEnabled()
	{
		return $this->switchToEditEnabled;
	}
}
