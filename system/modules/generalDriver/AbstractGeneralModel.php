<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

abstract class AbstractGeneralModel implements InterfaceGeneralModel
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