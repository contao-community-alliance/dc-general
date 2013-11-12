<?php

namespace DcGeneral\Contao\Dca\Populator;

use DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use DcGeneral\Data\DriverInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;
use DcGeneral\Exception\DcGeneralRuntimeException;

class DataProviderPopulator extends AbstractEventDrivenEnvironmentPopulator
{
	const PRIORITY = 100;

	public function populate(EnvironmentInterface $environment)
	{
		$definition = $environment->getDataDefinition();

		foreach ($definition->getDataProviderDefinition() as $dataProviderInformation)
		{
			if ($dataProviderInformation instanceof ContaoDataProviderInformation)
			{
				if ($environment->hasDataProvider($dataProviderInformation->getName()))
				{
					throw new DcGeneralRuntimeException('Data provider ' . $dataProviderInformation->getName() . ' already added to environment.');
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
