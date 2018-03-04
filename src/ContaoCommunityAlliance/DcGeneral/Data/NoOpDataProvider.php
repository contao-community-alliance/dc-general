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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Class NoOpDataProvider.
 *
 * Base implementation of an no operational data provider. This data provider is simply a stub endpoint without any
 * logic at all. It is useful as parent class for drivers that only implement a fraction of all DcGeneral features.
 */
class NoOpDataProvider implements DataProviderInterface
{
    /**
     * The configuration data for this instance.
     *
     * @var array
     */
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
        $model = new DefaultModel();
        $model->setProviderName(
            (isset($this->arrBaseConfig['name'])
                ? $this->arrBaseConfig['name']
                : $this->arrBaseConfig['source'])
        );
        return $model;
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
    public function getEmptyFilterOptionCollection()
    {
        trigger_error(
            'Method ' . __METHOD__ . ' was never intended to be called via interface and will get removed',
            E_USER_DEPRECATED
        );
        return new DefaultFilterOptionCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(ConfigInterface $objConfig)
    {
        return $this->getEmptyModel();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(ConfigInterface $objConfig)
    {
        return $this->getEmptyCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterOptions(ConfigInterface $objConfig)
    {
        return new DefaultFilterOptionCollection();
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
    public function save(ModelInterface $objItem, $timestamp = 0)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function saveEach(CollectionInterface $objItems, $timestamp = 0)
    {
        foreach ($objItems as $objItem) {
            $this->save($objItem, $timestamp);
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
        // @codingStandardsIgnoreStart
        @trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated - handle resetting manually', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd
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
    public function sameModels($objModel1, $objModel2)
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
