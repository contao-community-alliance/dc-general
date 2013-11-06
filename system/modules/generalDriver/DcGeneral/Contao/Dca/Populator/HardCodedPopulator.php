<?php

namespace DcGeneral\Contao\Dca\Populator;

use AbstractEventDrivenEnvironmentPopulator;
use DcGeneral\Callbacks\CallbacksInterface;
use DcGeneral\Callbacks\ContaoStyleCallbacks;
use DcGeneral\Clipboard\DefaultClipboard;
use DcGeneral\Contao\Dca\Section\ExtendedDca;
use DcGeneral\Contao\InputProvider;
use DcGeneral\Contao\TranslationManager;
use DcGeneral\Controller\ControllerInterface;
use DcGeneral\Controller\DefaultController;
use DcGeneral\DataDefinition\Section\BasicSectionInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\View\BackendView\ListView;
use DcGeneral\View\BackendView\ParentView;
use DcGeneral\View\BackendView\TreeView;
use DcGeneral\View\BackendView;
use DcGeneral\View\ViewInterface;

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

	/**
	 * Create a callback instance in the environment if none has been defined yet.
	 *
	 * NOTE: callback classes are deprecated due to the events used in DcGeneral.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @throws \DcGeneral\Exception\DcGeneralInvalidArgumentException
	 * @internal
	 */
	protected function populateCallback(EnvironmentInterface $environment)
	{
		// Already populated, get out then.
		if ($environment->getCallbackHandler())
		{
			return;
		}

		$callback = new ContaoStyleCallbacks();

		$callback->setDC($GLOBALS['objDcGeneral']);

		$environment->setCallbackHandler($callback);
	}

	/**
	 * Create a controller instance in the environment if none has been defined yet.
	 *
	 * @param EnvironmentInterface $environment
	 * @internal
	 */
	public function populateController(EnvironmentInterface $environment)
	{
		// Already populated, get out then.
		if ($environment->getController())
		{
			return;
		}

		$controller = new DefaultController();

		$controller->setEnvironment($environment);
		$environment->setController($controller);
	}

	/**
	 * {@inheritDoc}
	 */
	public function populate(EnvironmentInterface $environment)
	{
		if (!$environment->getInputProvider())
		{
			$environment->setInputProvider(new InputProvider());
		}

		if (!$environment->getClipboard())
		{
			$environment->setClipboard(new DefaultClipboard());
		}

		if (!$environment->getTranslationManager())
		{
			$environment->setTranslationManager(new TranslationManager());
		}

		$this->populateCallback($environment);
		$this->populateController($environment);
	}
}
