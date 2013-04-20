<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

interface InterfaceGeneralDataMultiLanguage extends InterfaceGeneralData
{

	/**
	 * Get all avaidable languages for a special record. 
	 * 
	 * @param mixed $mixID The ID of record
	 * @return InterfaceGeneralCollection 
	 */
	public function getLanguages($mixID);
	
	/**
	 * Get the fallback language
	 * 
	 * @param mixed $mixID The ID of record
	 * @return InterfaceGeneralModel
	 */
	public function getFallbackLanguage($mixID);

	/**
	 * Set the working language for the whole dataprovider.
	 *  
	 * @param $strLanguage The new language, use hort tag "2 chars like de, fr etc." 
	 * @return void
	 */
	public function setCurrentLanguage($strLanguage);

	/**
	 * Get the working language
	 * 
	 * return String Short tag for the current working language like de or fr etc. 
	 */
	public function getCurrentLanguage();

}