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

/**
 * Interface DataProviderSectionInterface
 *
 * @package DcGeneral\DataDefinition\Section
 */
class DefaultBasicSection implements BasicSectionInterface
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
