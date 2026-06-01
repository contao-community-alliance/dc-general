<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Class NoOpDataProvider.
 *
 * Base implementation of an no operational data provider. This data provider is simply a stub endpoint without any
 * logic at all. It is useful as parent class for drivers that only implement a fraction of all DcGeneral features.
 *
 * @psalm-suppress MissingConstructor - properties will get set in setBaseConfig().
 *
 * @api
 */
class NoOpDataProvider implements DataProviderInterface
{
    /**
     * The configuration data for this instance.
     *
     * @var array
     */
    protected array $arrBaseConfig;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setBaseConfig(array $config)
    {
        $this->arrBaseConfig = $config;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getEmptyConfig()
    {
        return DefaultConfig::init();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getEmptyModel()
    {
        $model = new DefaultModel();
        /** @psalm-suppress MixedArgument */
        $model->setProviderName(($this->arrBaseConfig['name'] ?? $this->arrBaseConfig['source']));
        return $model;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getEmptyCollection()
    {
        return new DefaultCollection();
    }

    /**
     * @return DefaultFilterOptionCollection
     */
    public function getEmptyFilterOptionCollection()
    {
        // phpcs:disable
        @\trigger_error(
            'Method ' . __METHOD__ . ' was never intended to be called via interface and will get removed',
            E_USER_DEPRECATED
        );
        // phpcs:enable
        return new DefaultFilterOptionCollection();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function fetch(ConfigInterface $config)
    {
        return $this->getEmptyModel();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function fetchAll(ConfigInterface $config)
    {
        return $this->getEmptyCollection();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getFilterOptions(ConfigInterface $config)
    {
        return new DefaultFilterOptionCollection();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getCount(ConfigInterface $config)
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function save(ModelInterface $item, $timestamp = 0)
    {
        return $item;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function saveEach(CollectionInterface $items, $timestamp = 0)
    {
        foreach ($items as $item) {
            $this->save($item, $timestamp);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function delete($item)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function saveVersion(ModelInterface $model, $username)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getVersion($mixID, $mixVersion)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getVersions($mixID, $onlyActive = false)
    {
        return new DefaultCollection();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setVersionActive($mixID, $mixVersion)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getActiveVersion($mixID)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function resetFallback($field)
    {
        // phpcs:disable
        @\trigger_error(
            __CLASS__ . '::' . __METHOD__ . ' is deprecated - handle resetting manually',
            E_USER_DEPRECATED
        );
        // phpcs:enable
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isUniqueValue($field, $new, $primaryId = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function fieldExists($columnName)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function sameModels($firstModel, $secondModel)
    {
        $firstProperties  = $firstModel->getPropertiesAsArray();
        $secondProperties = $secondModel->getPropertiesAsArray();

        $propertyNames = \array_unique(\array_merge(\array_keys($firstProperties), \array_keys($secondProperties)));
        foreach ($propertyNames as $propertyName) {
            if (
                !\array_key_exists($propertyName, $firstProperties) ||
                !\array_key_exists($propertyName, $secondProperties) ||
                $firstProperties[$propertyName] !== $secondProperties[$propertyName]
            ) {
                return false;
            }
        }

        return true;
    }
}
