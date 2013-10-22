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

namespace DcGeneral\Contao\Dca;

use DcGeneral\Contao\Dca\ListLabel;
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\Contao\Dca\Conditions\RootCondition;
use DcGeneral\Contao\Dca\Conditions\ParentChildCondition;
use DcGeneral\DataDefinition\PropertyInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;

class Container implements ContainerInterface
{
	/**
	 * The array used for fetching the values from
	 *
	 * @var array
	 */
	protected $arrDca;

	/**
	 * The table name to use for this DCA.
	 *
	 * @var string
	 */
	protected $strTable;

	/**
	 * All loaded properties.
	 *
	 * @var Property[]
	 */
	protected $properties;

	/**
	 * Create a new instance for the DCA of the passed name.
	 *
	 * @param string $strTable The table name.
	 *
	 * @param array  &$arrDca   The array to use.
	 *
	 */
	public function __construct($strTable, &$arrDca)
	{
		if (!(strlen($strTable) && is_array($arrDca) && count($arrDca)))
		{
			trigger_error('Could not load data container configuration', E_USER_ERROR);
		}

		$this->strTable = $strTable;
		$this->arrDca   = $arrDca;
	}

	public function getFromDca($strKey)
	{
		$chunks = explode('/', $strKey);
		$arrDca = $this->arrDca;

		while (($chunk = array_shift($chunks)) !== null)
		{
			if (!array_key_exists($chunk, $arrDca))
			{
				return null;
			}

			$arrDca = $arrDca[$chunk];
		}

		return $arrDca;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->strTable;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParentDriverName()
	{
		if ($this->getFromDca('config/ptable'))
		{
			return $this->getFromDca('config/ptable');
		}
		elseif ($this->getFromDca('dca_config/data_provider/parent/source'))
		{
			return $this->getFromDca('dca_config/data_provider/parent/source');
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLabel()
	{
		return $this->getFromDca('config/label');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIcon()
	{
		return $this->getFromDca('list/sorting/icon');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCallbackProviderClass()
	{
		$strCallbackClass = $this->getFromDca('dca_config/callback');
		if (!$strCallbackClass)
		{
			$strCallbackClass = '\DcGeneral\Callbacks\ContaoStyleCallbacks';
		}

		if (!class_exists($strCallbackClass))
		{
			throw new DcGeneralRuntimeException(sprintf('Invalid callback provider defined %s', var_export($strCallbackClass, true)));
		}

		return $strCallbackClass;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasProperties()
	{
		$properties = $this->getProperties();

		return (bool) $properties;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProperties()
	{
		if ($this->properties === null)
		{
			$this->properties = array();

			$dcaProperties = $this->getFromDca('fields');
			if ($dcaProperties)
			{
				foreach ($dcaProperties as $propertyName => $propertyConfig)
				{
					$this->properties[$propertyName] = new Property($this, $propertyName);
				}
			}
		}

		return $this->properties;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProperty($propertyName)
	{
		$properties = $this->getProperties();

		return $properties[$propertyName];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyNames()
	{
		$properties = $this->getProperties();

		return array_keys($properties);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasEditableProperties()
	{
		$properties = $this->getProperties();

		foreach ($properties as $property) {
			if ($property->isEditable()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditableProperties()
	{
		$properties = $this->getProperties();

		$properties = array_filter(
			$properties,
			function (Property $property) {
				return $property->isEditable();
			}
		);

		return $properties;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditablePropertyNames()
	{
		$properties = $this->getEditableProperties();

		$properties = array_map(
			function (Property $property) {
				return $property->getName();
			},
			$properties
		);

		return $properties;
	}

	/**
	 * Load a list with all editable field
	 *
	 * @param boolean $blnUserSelection
	 * @return boolean
	 */
	/*
	public function loadEditableFields()
	{
		$this->arrFields = array_flip(array_keys(array_filter($this->arrDCA['fields'], create_function('$arr', 'return !$arr[\'exclude\'];'))));
	}
	*/

	/**
	 * Check if the field is edtiable
	 *
	 * @param string $fieldName
	 * @return boolean
	 */
	/*
	public function isEditableField($fieldName)
	{
		return isset($this->arrFields[$fieldName]);
	}
	*/

	/**
	 * {@inheritDoc}
	 */
	public function getPanelLayout()
	{
		$arrPanels = explode(';', $this->getFromDca('list/sorting/panelLayout'));
		foreach ($arrPanels as $key => $strValue)
		{
			$arrPanels[$key] = array_filter(explode(',', $strValue));
		}

		return array_filter($arrPanels);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFirstSorting()
	{
		return $this->getFromDca('list/sorting/fields/0');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAdditionalSorting()
	{
		return $this->getFromDca('list/sorting/fields');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSortingMode()
	{
		return $this->getFromDca('list/sorting/mode');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSortingFlag()
	{
		return $this->getFromDca('list/sorting/flag');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOperation($strOperation)
	{
		if (!array_key_exists($strOperation, $this->arrDca['list']['operations']))
		{
			return null;
		}

		return new Operation($this, $strOperation);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOperationNames()
	{
		$arrOperations = $this->getFromDca('list/operations');
		if (!$arrOperations)
		{
			return array();
		}

		return array_keys($arrOperations);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isClosed()
	{
		return $this->getFromDca('config/closed');
	}

	/**
	 * {@inheritDoc}
	 */
	public function isEditable()
	{
		return !$this->getFromDca('config/notEditable');
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDeletable()
	{
	return !$this->getFromDca('config/notDeletable');
	}

	/**
	 * {@inheritDoc}
	 */
	public function isCreatable()
	{
		return !$this->getFromDca('config/notCreatable');
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSwitchToEdit()
	{
	return !$this->getFromDca('config/switchToEdit');
	}

	/**
	 * {@inheritDoc}
	 */
	public function isGroupingDisabled()
	{
		return !$this->getFromDca('list/sorting/disableGrouping');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRootCondition()
	{
		return new RootCondition($this, $this->getName());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChildCondition($strSrcTable, $strDstTable)
	{
		foreach ($this->getChildConditions($strSrcTable) as $objCondition)
		{
			if ($objCondition->getDestinationName() == $strDstTable)
			{
				return $objCondition;
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChildConditions($strSrcTable = '')
	{
		$arrConditions = $this->getFromDca('dca_config/childCondition');

		if (!is_array($arrConditions))
		{
			return array();
		}

		$arrReturn = array();
		foreach ($arrConditions as $intKey => $arrCondition)
		{
			if (empty($strSrcTable) || ($arrCondition['from'] != $strSrcTable))
			{
				continue;
			}

			$arrReturn[] = new ParentChildCondition($this, $intKey);
		}

		return $arrReturn;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getListLabel()
	{
		return new ListLabel($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParentViewHeaderProperties()
	{
		return $this->getFromDca('list/sorting/headerFields');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAdditionalFilter()
	{
		// Custom filter
		$filters = $this->getFromDca('list/sorting/filter');
		$result  = array();

		if (is_array($filters) && !empty($filters))
		{
			foreach ($filters as $filter)
			{
				// FIXME: this only takes array('name', 'value') into account. Add support for: array('name=?', 'value')
				$result[] = array('operation' => '=', 'property' => $filter[0], 'value' => $filter[1]);
			}
		}
		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPalettes()
	{
		$palettes = $this->getFromDca('palettes');
		if (is_array($palettes))
		{
			return $palettes;
		}
		else
		{
			return array();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSubPalettes()
	{
		$subPalettes = $this->getFromDca('subpalettes');
		if (is_array($subPalettes))
		{
			return $subPalettes;
		}
		else
		{
			return array();
		}
	}
}
