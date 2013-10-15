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

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\Contao\Dca\Conditions\RootCondition;
use DcGeneral\Contao\Dca\Conditions\ParentChildCondition;

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
	public function getCallbackProviderClass()
	{
		$strCallbackClass = $this->getFromDca('dca_config/callback');
		if (!$strCallbackClass)
		{
			$strCallbackClass = '\DcGeneral\Callbacks\ContaoStyleCallbacks';
		}

		if (!class_exists($strCallbackClass))
		{
			throw new \RuntimeException(sprintf('Invalid callback provider defined %s', var_export($strCallbackClass, true)));
		}

		return $strCallbackClass;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getProperty($strProperty)
	{
		if (!array_key_exists($strProperty, $this->arrDca['fields']))
		{
			return null;
		}

		return new Property($this, $strProperty);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyNames()
	{
		$arrProperties = $this->getFromDca('fields');
		if (!$arrProperties)
		{
			return array();
		}

		return array_keys($arrProperties);
	}

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

}
