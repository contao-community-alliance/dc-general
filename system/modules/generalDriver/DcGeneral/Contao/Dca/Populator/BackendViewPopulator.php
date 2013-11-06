<?php

namespace DcGeneral\Contao\Dca\Populator;

use AbstractEventDrivenEnvironmentPopulator;
use DcGeneral\DataDefinition\Section\BackendViewSectionInterface;
use DcGeneral\DataDefinition\Section\BasicSectionInterface;
use DcGeneral\DataDefinition\Section\View\Panel\FilterElementInformationInterface;
use DcGeneral\DataDefinition\Section\View\Panel\LimitElementInformationInterface;
use DcGeneral\DataDefinition\Section\View\Panel\SearchElementInformationInterface;
use DcGeneral\DataDefinition\Section\View\Panel\SortElementInformationInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Panel\DefaultFilterElement;
use DcGeneral\Panel\DefaultLimitElement;
use DcGeneral\Panel\DefaultPanel;
use DcGeneral\Panel\DefaultPanelContainer;
use DcGeneral\Panel\DefaultSearchElement;
use DcGeneral\Panel\DefaultSortElement;
use DcGeneral\Panel\DefaultSubmitElement;
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
	 * Create a panel instance in the view if none has been defined yet.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @throws DcGeneralRuntimeException
	 * @internal
	 */
	protected function populatePanel(EnvironmentInterface $environment)
	{
		// Already populated or not in Backend? Get out then.
		if (!(($environment->getView() instanceof BaseView) && (TL_MODE == 'BE')))
		{
			return;
		}

		$definition = $environment->getDataDefinition();

		if (!$definition->hasSection(BackendViewSectionInterface::NAME))
		{
			return;
		}

		/**  @var BackendViewInterface $view */
		$view = $environment->getView();

		// Already populated.
		if ($view->getPanel())
		{
			return;
		}

		$panel = new DefaultPanelContainer();
		$panel->setEnvironment($environment);
		$view->setPanel($panel);

		/** @var BackendViewSectionInterface $section  */
		$section = $definition->getSection(BackendViewSectionInterface::NAME);

		/** @var \DcGeneral\DataDefinition\Section\View\PanelLayoutInterface $panelLayout */
		$panelLayout = $section->getPanelLayout();

		$rows         = $panelLayout->getRows();
		$objPanel     = null;
		$lastPanelKey = $rows->getRowCount();

		foreach ($panelLayout->getRows() as $panelKey => $row)
		{
			// We need a new panel.
			$panelRow = new DefaultPanel();

			$panel->addPanel($panelKey, $panelRow);

			// TODO: this is maybe not as elegant as it should be.
			if ($panelKey == $lastPanelKey)
			{
				$panelRow->addElement('submit', new DefaultSubmitElement());
			}

			foreach ($row as $element)
			{
				if ($element instanceof FilterElementInformationInterface)
				{
					$panelElement = new DefaultFilterElement();
					$panelElement->setPropertyName($element->getPropertyName());
					$panelRow->addElement($element->getName(), $panelElement);
				}
				elseif ($element instanceof LimitElementInformationInterface)
				{
					$panelElement = new DefaultLimitElement();
					$panelRow->addElement($element->getName(), $panelElement);
				}
				elseif ($element instanceof SearchElementInformationInterface)
				{
					$panelElement = new DefaultSearchElement();

					foreach ($element->getPropertyNames() as $propName)
					{
						$panelElement->addProperty($propName);
					}

					$panelRow->addElement($element->getName(), $panelElement);
				}
				elseif ($element instanceof SortElementInformationInterface)
				{
					$panelElement = new DefaultSortElement();

					foreach ($element->getPropertyNames() as $propName)
					{
						$panelElement->addProperty($propName,  $element->getPropertyFlag($propName));
					}

					$panelRow->addElement($element->getName(), $panelElement);
				}
			}
		}
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

		$this->populatePanel($environment);
	}
}
