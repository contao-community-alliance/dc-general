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

namespace DcGeneral\Data;

/**
 * Class MultiLanguageDataProvider.
 * Implementation of a multi language Contao database data provider.
 *
 * The default language will be initialized to "en".
 *
 * @package DcGeneral\Data
 */
class MultiLanguageDataProvider extends DefaultDataProvider implements MultiLanguageDataProviderInterface
{
	/**
	 * Buffer to keep the current active working language.
	 *
	 * @var string
	 */
	protected $strCurrentLanguage;

	/**
	 * Constructor - initializes the object with English as working language.
	 */
	public function __construct()
	{
		$this->setCurrentLanguage('en');

		parent::__construct();
	}

	/**
	 * Get all available languages of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
	 * @return LanguageInformationCollectionInterface
	 */
	public function getLanguages($mixID)
	{
		$collection = new DefaultLanguageInformationCollection();

		// FIXME: hardcoded languages "German" and "English".
		$collection
			->add(new DefaultLanguageInformation('de', null))
			->add(new DefaultLanguageInformation('en', null));

		return $collection;
	}

	/**
	 * Get the fallback language of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
	 * @return LanguageInformationInterface
	 */
	public function getFallbackLanguage($mixID)
	{
		// FIXME: hardcoded fallback language "English".
		return new DefaultLanguageInformation('en', null);
	}

	/**
	 * Set the current working language for the whole data provider.
	 *
	 * @param string $strLanguage The new language, use short tag "2 chars like de, fr etc.".
	 *
	 * @return DataProviderInterface
	 */
	public function setCurrentLanguage($strLanguage)
	{
		$this->strCurrentLanguage = $strLanguage;

		return $this;
	}

	/**
	 * Get the current working language.
	 *
	 * @return string Short tag for the current working language like de or fr etc.
	 */
	public function getCurrentLanguage()
	{
		return $this->strCurrentLanguage;
	}
}
