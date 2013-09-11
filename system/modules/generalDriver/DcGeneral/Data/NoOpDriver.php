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

namespace DcGeneral\Data;

class NoOpDriver implements DriverInterface
{
	protected $arrBaseConfig;

	/**
	 * {@inheritdoc}
	 */
	public function setBaseConfig(array $arrConfig)
	{
		$this->arrBaseConfig = $arrConfig;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyConfig()
	{
		return DefaultConfig::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyModel()
	{
		return new DefaultModel();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyCollection()
	{
		return new DefaultCollection();
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch(ConfigInterface $objConfig)
	{
		return new DefaultModel();
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetchAll(ConfigInterface $objConfig)
	{
		return new DefaultCollection();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilterOptions(ConfigInterface $objConfig)
	{
		return new DefaultCollection();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCount(ConfigInterface $objConfig)
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	 public function save(ModelInterface $objItem)
	 {
	 }

	/**
	 * {@inheritdoc}
	 */
	public function saveEach(CollectionInterface $objItems)
	{
		foreach ($objItems as $objItem) {
			$this->save($objItem);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($item)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveVersion(ModelInterface $objModel, $strUsername)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVersion($mixID, $mixVersion)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVersions($mixID, $blnOnlyActive = false)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setVersionActive($mixID, $mixVersion)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getActiveVersion($mixID)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resetFallback($strField)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function isUniqueValue($strField, $varNew, $intId = null)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function fieldExists($strField)
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function sameModels($objModel1 , $objModel2)
	{
		$arrProperties1 = $objModel1->getPropertiesAsArray();
		$arrProperties2 = $objModel2->getPropertiesAsArray();

		$arrKeys = array_merge(array_keys($arrProperties1), array_keys($arrProperties2));
		$arrKeys = array_unique($arrKeys);
		foreach ($arrKeys as $strKey) {
			if (!array_key_exists($strKey, $arrProperties1) ||
				!array_key_exists($strKey, $arrProperties2) ||
				$arrProperties1[$strKey] != $arrProperties2[$strKey]
			) {
				return false;
			}
		}

		return true;
	}
}
