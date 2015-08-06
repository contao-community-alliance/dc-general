<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * This interface describes a language information consisting of language code(ISO 639) and country code(ISO 3166).
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
