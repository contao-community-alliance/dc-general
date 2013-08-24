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

interface MultiLanguageDriverInterface extends DriverInterface
{
	/**
	 * Get all available languages of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
	 * @todo: using Collection here is perversion. We need to change this!
	 * @return CollectionInterface
	 */
	public function getLanguages($mixID);

	/**
	 * Get the fallback language of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
	 * @todo: using Model here is perversion. We need to change this!
	 * @return ModelInterface
	 */
	public function getFallbackLanguage($mixID);

	/**
	 * Set the current working language for the whole data provider.
	 *
	 * @param string $strLanguage The new language, use short tag "2 chars like de, fr etc."
	 *
	 * @return DriverInterface
	 */
	public function setCurrentLanguage($strLanguage);

	/**
	 * Get the current working language.
	 *
	 * @return string Short tag for the current working language like de or fr etc.
	 */
	public function getCurrentLanguage();
}
