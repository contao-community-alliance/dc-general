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
	 * Get all available languages of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
	 * @return InterfaceGeneralCollection
	 */
	public function getLanguages($mixID);

	/**
	 * Get the fallback language of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
	 * @return InterfaceGeneralModel
	 */
	public function getFallbackLanguage($mixID);

	/**
	 * Set the current working language for the whole data provider.
	 *
	 * @param string $strLanguage The new language, use short tag "2 chars like de, fr etc."
	 *
	 * @return void
	 */
	public function setCurrentLanguage($strLanguage);

	/**
	 * Get the current working language.
	 *
	 * @return string Short tag for the current working language like de or fr etc.
	 */
	public function getCurrentLanguage();
}
