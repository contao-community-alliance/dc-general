<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Config;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentFlatConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Registry for default data provider configurations to only resolve them once.
 * The flat config registry is there to map a data definition that has a parent definition as a flat.
 * In this registry, no filtering or similar is applied by the parent.
 */
final class FlatConfigRegistry implements BaseConfigRegistryInterface
{
    /**
     * The attached environment.
     *
     * @var EnvironmentFlatConfigRegistryInterface|null
     */
    private $environment;

    /**
     * {@inheritDoc}
     */
    public function getBaseConfig(ModelIdInterface $parentId = null): ConfigInterface
    {
        $environment = $this->getEnvironment();
        $config      = $environment->getDataProvider()->getEmptyConfig();
        $definition  = $environment->getDataDefinition();
        $additional  = $definition->getBasicDefinition()->getAdditionalFilter();

        // Custom filter common for all modes.
        if ($additional) {
            $config->setFilter($additional);
        }

        $this->addDefaultSorting($config);

        return $config;
    }

    /**
     * Add the default sorting fields.
     *
     * @param ConfigInterface $config The data provider config.
     *
     * @return void
     */
    private function addDefaultSorting(ConfigInterface $config): void
    {
        if (!empty($config->getSorting())) {
            return;
        }

        $environment = $this->getEnvironment();
        $definition  = $environment->getDataDefinition();
        /** @var Contao2BackendViewDefinitionInterface $viewDefinition */
        $viewDefinition = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $config->setSorting($viewDefinition->getListingConfig()->getDefaultSortingFields());
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvironment(): ?EnvironmentFlatConfigRegistryInterface
    {
        return $this->environment;
    }

    /**
     * Set the environment.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return FlatConfigRegistry
     */
    public function setEnvironment(EnvironmentInterface $environment): FlatConfigRegistry
    {
        $this->environment = $environment;

        return $this;
    }
}
