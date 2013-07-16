<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Contao\Dca;

use DcGeneral\DataDefinition\Interfaces\Container as ContainerInterface;

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
	 * @param array  $arrDca   The array to use.
	 *
	 */
	public function __construct($strTable, $arrDca)
	{
		$this->strTable = $strTable;
		$this->arrDca   = $arrDca;
	}

	public function getFromDca($strKey)
	{
		$chunks = explode('/', $strKey);
		$arrDca = $this->arrDca;

		while ($chunk = array_shift($chunks))
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
	public function getProperty($strProperty)
	{
		if (!array_key_exists($strProperty, $this->arrDca['fields']))
		{
			return null;
		}

		return new Property($this, $strProperty);
	}

	public function getPropertyNames()
	{
		return array_keys($this->getFromDca('fields'));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPanelLayout()
	{
		$arrPanels = explode(';', $this->getFromDca('list/sorting/panelLayout'));
		foreach ($arrPanels as $key => $strValue)
		{
			$arrPanels[$key] = explode(',', $strValue);
		}

		return $arrPanels;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAdditionalSorting()
	{
		return $this->getFromDca('list/sorting/fields');
	}
}
