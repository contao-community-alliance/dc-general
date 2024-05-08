<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use LogicException;

use function array_merge;

/**
 * Registry for default data provider configurations to only resolve them once.
 */
class BaseConfigRegistry implements BaseConfigRegistryInterface
{
    /**
     * The attached environment.
     *
     * @var EnvironmentInterface|null
     */
    private ?EnvironmentInterface $environment = null;

    /**
     * The cached configurations.
     *
     * @var array<string, ConfigInterface>
     */
    private array $configs = [];

    /**
     * Set the environment to use.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return BaseConfigRegistry
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvironment()
    {
        if (null === $this->environment) {
            throw new LogicException('Property environment not initialized');
        }

        return $this->environment;
    }

    /**
     * Add the filter for the item with the given id from the parent data provider to the given config.
     *
     * @param ModelIdInterface $idParent The id of the parent item.
     * @param ConfigInterface  $config   The config to add the filter to.
     *
     * @return ConfigInterface
     *
     * @throws DcGeneralRuntimeException When the parent item is not found.
     */
    private function addParentFilter(ModelIdInterface $idParent, ConfigInterface $config): ConfigInterface
    {
        $environment = $this->getEnvironment();
        $definition  = $environment->getDataDefinition();
        if (null === $definition) {
            throw new DcGeneralRuntimeException('Data definition not set.');
        }
        $basicDefinition    = $definition->getBasicDefinition();
        $providerName       = $basicDefinition->getDataProvider();
        $parentProviderName = $idParent->getDataProviderName();
        $parentProvider     = $environment->getDataProvider($parentProviderName);

        if ($basicDefinition->getParentDataProvider() !== $parentProviderName) {
            throw new DcGeneralRuntimeException(
                'Unexpected parent provider ' . $parentProviderName .
                ' (expected ' . ((string) $basicDefinition->getParentDataProvider()) . ')'
            );
        }

        if ($parentProvider) {
            $parent = $parentProvider->fetch($parentProvider->getEmptyConfig()->setId($idParent->getId()));
            if (!$parent) {
                throw new DcGeneralRuntimeException(
                    'Parent item ' . $idParent->getSerialized() . ' not found in ' . $parentProviderName
                );
            }

            $condition = $definition->getModelRelationshipDefinition()->getChildCondition(
                $parentProviderName,
                (string) $providerName
            );

            if ($condition) {
                $baseFilter = $config->getFilter();
                $filter     = $condition->getFilter($parent);

                if (\is_array($baseFilter)) {
                    $filter = array_merge($baseFilter, $filter);
                }

                $config->setFilter([['operation' => 'AND', 'children'  => $filter]]);
            }
        }

        return $config;
    }

    /**
     * Retrieve the base data provider config for the current data definition.
     *
     * This includes parent filter when in parented list mode and the additional filters from the data definition.
     *
     * @param ModelIdInterface|null $parentId The parent to use.
     *
     * @return ConfigInterface
     */
    private function buildBaseConfig(?ModelIdInterface $parentId): ConfigInterface
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);
        $provider    = $environment->getDataProvider();
        if (null === $provider) {
            throw new DcGeneralRuntimeException('Data provider not set.');
        }
        $config      = $provider->getEmptyConfig();
        $definition  = $environment->getDataDefinition();
        if (null === $definition) {
            throw new DcGeneralRuntimeException('Data definition not set.');
        }
        $additional = $definition->getBasicDefinition()->getAdditionalFilter();

        // Custom filter common for all modes.
        if (\is_array($additional)) {
            $config->setFilter($additional);
        }

        if (!$config->getSorting()) {
            /** @var Contao2BackendViewDefinitionInterface $viewDefinition */
            $viewDefinition = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
            /** @psalm-suppress DeprecatedMethod - we can not change this in 2.x */
            $config->setSorting($viewDefinition->getListingConfig()->getDefaultSortingFields());
        }

        // Special filter for certain modes.
        if ($parentId) {
            $this->addParentFilter($parentId, $config);
        } elseif (BasicDefinitionInterface::MODE_PARENTEDLIST === $definition->getBasicDefinition()->getMode()) {
            $input = $environment->getInputProvider();
            if (null === $input) {
                throw new DcGeneralRuntimeException('Input provider not set.');
            }
            $pid        = $input->getParameter('pid');
            $pidDetails = ModelId::fromSerialized($pid);

            $this->addParentFilter($pidDetails, $config);
        }

        return $config;
    }

    /**
     * Retrieve the base data provider config for the current data definition.
     *
     * This includes parent filter when in parented list mode and the additional filters from the data definition.
     *
     * @param ModelIdInterface $parentId The optional parent to use.
     *
     * @return ConfigInterface
     */
    public function getBaseConfig(ModelIdInterface $parentId = null)
    {
        $key = $parentId ? $parentId->getSerialized() : '';

        if (!isset($this->configs[$key])) {
            $this->configs[$key] = $this->buildBaseConfig($parentId);
        }

        return clone $this->configs[$key];
    }
}
