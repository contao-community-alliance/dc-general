<?php

namespace DcGeneral\Contao\Dca\Populator;

use AbstractEventDrivenEnvironmentPopulator;
use DcGeneral\Clipboard\DefaultClipboard;
use DcGeneral\Contao\InputProvider;
use DcGeneral\Contao\TranslationManager;
use DcGeneral\EnvironmentInterface;

/**
 * Class HardCodedPopulator
 *
 * This class only exists to have some intermediate hardcoded transition point until the builder ans populators have been
 * properly coded. This class will then be removed from the code base.
 *
 * @package DcGeneral\Contao\Dca\Populator
 */
class HardCodedPopulator extends AbstractEventDrivenEnvironmentPopulator
{
	const PRIORITY = 1000;

	public function populate(EnvironmentInterface $environment)
	{
		$definition = $environment->getDataDefinition();

		$environment
			->setInputProvider(new InputProvider())
			->setClipboard(new DefaultClipboard())
			->setTranslationManager(new TranslationManager());
	}
}
