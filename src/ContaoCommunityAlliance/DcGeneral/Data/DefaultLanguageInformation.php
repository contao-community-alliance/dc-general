<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * This class is the base implementation for LanguageInformationInterface.
 */
class DefaultLanguageInformation implements LanguageInformationInterface
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
        if ($this->getCountryCode()) {
            return $this->getLanguageCode() . '_' . $this->getCountryCode();
        }

        return $this->getLanguageCode();
    }
}
