<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use ContaoCommunityAlliance\DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Class DataProviderPopulator.
 *
 * This class reacts to the PopulateEnvironmentEvent and populates the environment with all data providers implementing
 * the interface ContaoDataProviderInformation.
 */
class DataProviderPopulator extends AbstractEventDrivenEnvironmentPopulator
{
    public const PRIORITY = 100;

    /**
     * The cached instances of the data provider.
     *
     * @var array<string, DataProviderInterface>
     */
    private $instances = [];

    /**
     * Creates an instance of itself and processes the event.
     *
     * The attached environment {@link EnvironmentInterface} will be populated
     * with the information from the builder's data source.
     *
     * @param PopulateEnvironmentEvent $event The event to process.
     *
     * @return void
     */
    public function processEvent(PopulateEnvironmentEvent $event): void
    {
        $this->populate($event->getEnvironment());
    }

    /**
     * Instantiates and adds the data providers implementing ContaoDataProviderInformation to the environment.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When a data provider has already been added to the environment.
     */
    public function populate(EnvironmentInterface $environment)
    {
        if (null === $definition = $environment->getDataDefinition()) {
            return;
        }
        foreach ($definition->getDataProviderDefinition() as $information) {
            if ($information instanceof ContaoDataProviderInformation) {
                if ($environment->hasDataProvider($information->getName())) {
                    throw new DcGeneralRuntimeException(
                        \sprintf(
                            'Data provider %s already added to environment.',
                            $information->getName()
                        )
                    );
                }

                $initializationData = $information->getInitializationData();
                \ksort($initializationData);
                $cacheKey = \md5(\json_encode($initializationData) . $information->getClassName());
                if (!isset($this->instances[$cacheKey])) {
                    /** @var DataProviderInterface $dataProvider */
                    $dataProvider = (new \ReflectionClass($information->getClassName()))->newInstance();
                    if ($initializationData) {
                        $dataProvider->setBaseConfig($initializationData);
                    }
                    $this->instances[$cacheKey] = $dataProvider;
                }

                $environment->addDataProvider($information->getName(), $this->instances[$cacheKey]);
            }
        }
    }
}
