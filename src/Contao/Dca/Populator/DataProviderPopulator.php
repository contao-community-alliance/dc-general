<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use ContaoCommunityAlliance\DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class DataProviderPopulator.
 *
 * This class reacts to the PopulateEnvironmentEvent and populates the environment with all data providers implementing
 * the interface ContaoDataProviderInformation.
 */
class DataProviderPopulator extends AbstractEventDrivenEnvironmentPopulator
{
    const PRIORITY = 100;

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
        $definition = $environment->getDataDefinition();

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

                $providerClass = new \ReflectionClass($information->getClassName());

                /** @var DataProviderInterface $dataProvider */
                $dataProvider = $providerClass->newInstance();
                if ($initializationData = $information->getInitializationData()) {
                    $dataProvider->setBaseConfig($initializationData);
                }

                $environment->addDataProvider($information->getName(), $dataProvider);
            }
        }
    }
}
