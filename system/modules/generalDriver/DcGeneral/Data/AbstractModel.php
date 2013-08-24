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

namespace DcGeneral\Data;

use DcGeneral\Data\ModelInterface;

abstract class AbstractModel implements ModelInterface
{
	/**
	 * A list with all meta information.
	 *
	 * @var array
	 */
	protected $arrMetaInformation = array();

	/**
	 * {@inheritdoc}
	 */
	public function getMeta($strMetaName)
	{
		if (isset($this->arrMetaInformation[$strMetaName]))
		{
			return $this->arrMetaInformation[$strMetaName];
		}
		else
		{
			return null;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setMeta($strMetaName, $varValue)
	{
		$this->arrMetaInformation[$strMetaName] = $varValue;
	}
}
