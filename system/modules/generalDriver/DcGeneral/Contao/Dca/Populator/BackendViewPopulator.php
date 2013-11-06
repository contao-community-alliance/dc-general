<?php

namespace DcGeneral\Contao\Dca\Populator;

use AbstractEventDrivenEnvironmentPopulator;
use DcGeneral\DataDefinition\Section\BackendViewSectionInterface;
use DcGeneral\DataDefinition\Section\BasicSectionInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\View\BackendView\BackendViewInterface;
use DcGeneral\View\BackendView\BaseView;
use DcGeneral\View\BackendView\ListView;
use DcGeneral\View\BackendView\ParentView;
use DcGeneral\View\BackendView\TreeView;

/**
 * Class BackendViewPopulator
 *
 * This class is the default fallback populator in the Contao Backend to instantiate a BackendView.
 *
 * @package DcGeneral\Contao\Dca\Populator
 */
class BackendViewPopulator extends AbstractEventDrivenEnvironmentPopulator
{
	const PRIORITY = 100;

	/**
	 * Create a view instance in the environment if none has been defined yet.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @throws DcGeneralInvalidArgumentException
	 * @internal
	 */
	protected function populateView(EnvironmentInterface $environment)
	{
		// Already populated or not in Backend? Get out then.
		if ($environment->getView() || (TL_MODE != 'BE'))
		{
			return;
		}

		$definition = $environment->getDataDefinition();

		if (!$definition->hasBasicSection())
		{
			return;
		}

		$section = $definition->getBasicSection();

		switch ($section->getMode())
		{
			case BasicSectionInterface::MODE_FLAT:
				$view = new ListView();
				break;
			case BasicSectionInterface::MODE_PARENTEDLIST:
				$view = new ParentView();
				break;
			case BasicSectionInterface::MODE_HIERARCHICAL:
				$view = new TreeView();
				break;
			default:
				throw new DcGeneralInvalidArgumentException('Unknown view mode encountered: ' . $section->getMode());
		}

		$view->setEnvironment($environment);
		$environment->setView($view);
	}

	/**
	 * Create a view instance in the environment if none has been defined yet.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @throws \DcGeneral\Exception\DcGeneralInvalidArgumentException
	 */
	public function populate(EnvironmentInterface $environment)
	{
		$this->populateView($environment);
	}
}
