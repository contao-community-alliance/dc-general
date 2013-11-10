<?php

namespace DcGeneral\Contao\Dca\Populator;

use DcGeneral\Callbacks\CallbacksInterface;
use DcGeneral\Contao\Dca\Definition\ExtendedDca;
use DcGeneral\Controller\ControllerInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;
use DcGeneral\View\ViewInterface;

/**
 * Class HardCodedPopulator
 *
 * This class only exists to have some intermediate hardcoded transition point until the builder ans populators have been
 * properly coded. This class will then be removed from the code base.
 *
 * @package DcGeneral\Contao\Dca\Populator
 */
class ExtendedLegacyDcaPopulator extends AbstractEventDrivenEnvironmentPopulator
{
	const PRIORITY = 100;

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
		$definition = $environment->getDataDefinition();

		// If we encounter an extended definition, that one may override.
		if (!$definition->hasDefinition(ExtendedDca::NAME))
		{
			return;
		}

		/** @var ExtendedDca $definition */
		$definition = $definition->getDefinition(ExtendedDca::NAME);
		$class   = $definition->getCallbackClass();

		if (!$class)
		{
			return;
		}

		$callbackClass = new \ReflectionClass($class);
		/** @var CallbacksInterface $callback */
		$callback = $callbackClass->newInstance();

		$callback->setDC($GLOBALS['objDcGeneral']);
		$environment->setCallbackHandler($callback);
	}

	/**
	 * Create a view instance in the environment if none has been defined yet.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @throws \DcGeneral\Exception\DcGeneralInvalidArgumentException
	 * @internal
	 */
	protected function populateView(EnvironmentInterface $environment)
	{
		// Already populated, get out then.
		if ($environment->getView())
		{
			return;
		}

		$definition = $environment->getDataDefinition();

		// If we encounter an extended definition, that one may override.
		if (!$definition->hasDefinition(ExtendedDca::NAME))
		{
			return;
		}

		/** @var ExtendedDca $definition */
		$definition = $definition->getDefinition(ExtendedDca::NAME);
		$class   = $definition->getViewClass();

		if (!$class)
		{
			return;
		}

		$viewClass = new \ReflectionClass($class);

		/** @var ViewInterface $view */
		$view = $viewClass->newInstance();

		$view->setEnvironment($environment);
		$environment->setView($view);
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

		$definition = $environment->getDataDefinition();

		// If we encounter an extended definition, that one may override.
		if (!$definition->hasDefinition(ExtendedDca::NAME))
		{
			return;
		}

		/** @var ExtendedDca $definition */
		$definition = $definition->getDefinition(ExtendedDca::NAME);
		$class   = $definition->getControllerClass();

		if (!$class)
		{
			return;
		}

		$controllerClass = new \ReflectionClass($class);

		/** @var ControllerInterface $controller */
		$controller = $controllerClass->newInstance();

		$controller->setEnvironment($environment);
		$environment->setController($controller);
	}

	/**
	 * {@inheritDoc}
	 */
	public function populate(EnvironmentInterface $environment)
	{
		$this->populateCallback($environment);
		$this->populateView($environment);
		$this->populateController($environment);
	}
}
