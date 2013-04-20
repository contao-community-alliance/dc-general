<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

interface InterfaceGeneralController
{

	/**
	 * Set the DC
	 * 
	 * @param DC_General $objDC
	 */
	public function setDC($objDC);

	/**
	 * Get the DC
	 * 
	 * @return DC_General 
	 */
	public function getDC();

}