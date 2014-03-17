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

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * This class is the base implementation for LanguageInformationInterface.
 *
 * @package DcGeneral\Data
 */
class DefaultLanguageInformation
	implements LanguageInformationInterface
{
	/**
	 * The ISO 639 language code.
	 *
	 * @var string
	 */
	protected $language;

	/**
	 * The ISO 3166 country code.
	 *
	 * @var string
	 */
	protected $country;

	/**
	 * Create a new instance.
	 *
	 * @param string      $language The ISO 639 language code.
	 *
	 * @param null|string $country  The ISO 3166 country code.
	 */
	public function __construct($language, $country = null)
	{
		$this->language = $language;
		$this->country  = $country;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLanguageCode()
	{
		return $this->language;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCountryCode()
	{
		return $this->country;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLocale()
	{
		if ($this->getCountryCode())
		{
			return $this->getLanguageCode() . '_' . $this->getCountryCode();
		}

		return $this->getLanguageCode();
	}
}
