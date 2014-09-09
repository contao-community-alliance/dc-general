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

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

/**
 * Default implementation of a sort element.
 *
 * @package DcGeneral\Panel
 */
class DefaultSortElement
	extends AbstractElement
	implements SortElementInterface
{
	/**
	 * The selected definition.
	 *
	 * @var GroupAndSortingDefinitionInterface
	 */
	protected $selected;

	/**
	 * The default flag to use.
	 *
	 * @var int
	 *
	 * @deprecated Not in use anymore.
	 */
	public $intDefaultFlag;

	/**
	 * The sorting properties including the direction.
	 *
	 * @var array
	 */
	protected $arrSorting = array();

	/**
	 * Retrieve the group and sorting definition.
	 *
	 * @return GroupAndSortingDefinitionCollectionInterface
	 */
	protected function getGroupAndSortingDefinition()
	{
		/** @var Contao2BackendViewDefinitionInterface $view */
		$view = $this->getEnvironment()
			->getDataDefinition()
			->getDefinition(Contao2BackendViewDefinitionInterface::NAME);

		return $view
			->getListingConfig()
			->getGroupAndSortingDefinition();
	}

	/**
	 * Search a definition by it's name.
	 *
	 * @param string $name The name.
	 *
	 * @return GroupAndSortingDefinitionInterface|null
	 */
	protected function searchDefinitionByName($name)
	{
		foreach ($this->getGroupAndSortingDefinition() as $definition)
		{
			/** @var GroupAndSortingDefinitionInterface $definition */
			if ($definition->getName() == $name)
			{
				return $definition;
			}
		}

		return null;
	}

	/**
	 * Retrieve the persistent value from the input provider.
	 *
	 * @return array
	 */
	protected function getPersistent()
	{
		$arrValue = array();
		if ($this->getInputProvider()->hasPersistentValue('sorting'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('sorting');
		}

		if (array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $arrValue))
		{
			return $arrValue[$this->getEnvironment()->getDataDefinition()->getName()];
		}

		return array();
	}

	/**
	 * Store the persistent value in the input provider.
	 *
	 * @param string $strProperty The name of the property to sort by.
	 *
	 * @return void
	 */
	protected function setPersistent($strProperty)
	{
		$arrValue       = array();
		$definitionName = $this->getEnvironment()->getDataDefinition()->getName();

		if ($this->getInputProvider()->hasPersistentValue('sorting'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('sorting');
		}

		if ($strProperty)
		{
			if (!is_array($arrValue[$definitionName]))
			{
				$arrValue[$definitionName] = array();
			}
			$arrValue[$definitionName] = $strProperty;
		}
		else
		{
			unset($arrValue[$definitionName]);
		}

		$this->getInputProvider()->setPersistentValue('sorting', $arrValue);
	}

	/**
	 * {@inheritDoc}
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
	{
		if (is_null($objElement))
		{
			$input = $this->getInputProvider();
			$value = null;

			if ($this->getPanel()->getContainer()->updateValues() && $input->hasValue('tl_sort'))
			{
				$value = $input->getValue('tl_sort');

				$this->setPersistent($value);

			}

			$this->setSelected($this->getPersistent());
		}

		$current = $objConfig->getSorting();

		if (!is_array($current))
		{
			$current = array();
		}

		if ($this->getSelectedDefinition())
		{
			foreach ($this->getSelectedDefinition() as $information)
			{
				/** @var GroupAndSortingInformationInterface $information */
				$current[$information->getProperty()] = $information->getSortingMode();
			}
		}

		$objConfig->setSorting(array_reverse($current, true));
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(ViewTemplateInterface $objTemplate)
	{
		$arrOptions = array();
		foreach ($this->getGroupAndSortingDefinition() as $information)
		{
			/** @var GroupAndSortingDefinitionInterface $information */
			$name       = $information->getName();
			$properties = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();
			if ($properties->hasProperty($name))
			{
				$name = $properties->getProperty($name)->getLabel();
			}

			if (empty($name))
			{
				$name = $information->getName();
			}

			$arrOptions[] = array(
				'value'      => specialchars($information->getName()),
				'attributes' => ($this->getSelected() == $information->getName()) ? ' selected="selected"' : '',
				'content'    => $name
			);
		}

		// Sort by option values.
		uksort($arrOptions, 'strcasecmp');

		$objTemplate->options = $arrOptions;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function setDefaultFlag($intFlag)
	{
		$this->intDefaultFlag = $intFlag;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function getDefaultFlag()
	{
		return $this->intDefaultFlag;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function addProperty($strPropertyName, $intFlag)
	{
		// NO OP as of now.
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function getPropertyNames()
	{
		$names = array();
		foreach ($this->getGroupAndSortingDefinition() as $definition)
		{
			/** @var GroupAndSortingDefinitionInterface $definition */
			$names[] = $definition->getName();
		}

		return $names;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSelected($name)
	{
		$this->selected = $this->searchDefinitionByName($name);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSelected()
	{
		return $this->selected ? $this->selected->getName() : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSelectedDefinition()
	{
		return $this->selected;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated not in use anymore.
	 */
	public function getFlag()
	{
		return 0;
	}
}
