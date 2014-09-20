<?php
/**
 * PHP version 5
 *
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
 * This interface describes a language information consisting of language code(ISO 639) and country code(ISO 3166).
 *
 * @package DcGeneral\Data
 */
interface LanguageInformationInterface
{
    /**
     * Retrieve the ISO 639 language code.
     *
     * @return string
     */
    public function getLanguageCode();

    /**
     * Retrieve the ISO 3166 country code.
     *
     * @return string
     */
    public function getCountryCode();

    /**
     * Retrieve the RFC 3066 locale string.
     *
     * This string is combined as language-code + "_" + country-code.
     *
     * If no country code has been set, the language code only will get returned.
     *
     * @return mixed
     */
    public function getLocale();
}
