<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
 *
 * @package DcGeneral\Contao\Dca\Populator
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
                        sprintf(
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
