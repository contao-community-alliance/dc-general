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

namespace DcGeneral\Contao\Dca\Populator;

use DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use DcGeneral\Data\DriverInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;
use DcGeneral\Exception\DcGeneralRuntimeException;

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

		foreach ($definition->getDataProviderDefinition() as $dataProviderInformation)
		{
			if ($dataProviderInformation instanceof ContaoDataProviderInformation)
			{
				if ($environment->hasDataProvider($dataProviderInformation->getName()))
				{
					throw new DcGeneralRuntimeException(sprintf(
						'Data provider %s already added to environment.',
						$dataProviderInformation->getName()
					));
				}

				$providerClass = new \ReflectionClass($dataProviderInformation->getClassName());

				/** @var DriverInterface $dataProvider */
				$dataProvider = $providerClass->newInstance();
				if ($initializationData = $dataProviderInformation->getInitializationData())
				{
					$dataProvider->setBaseConfig($initializationData);
				}

				$environment->addDataProvider($dataProviderInformation->getName(), $dataProvider);
			}
		}
	}
}
