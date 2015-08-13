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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Interface MultiLanguageDataProviderInterface.
 *
 * This interface describes how to interface with a multi language data provider.
 */
interface MultiLanguageDataProviderInterface extends DataProviderInterface
{
    /**
     * Get all available languages of a certain record.
     *
     * @param mixed $mixID The ID of the record to retrieve.
     *
     * @return LanguageInformationCollectionInterface|null
     */
    public function getLanguages($mixID);

    /**
     * Get the fallback language of a certain record.
     *
     * @param mixed $mixID The ID of the record to retrieve.
     *
     * @return LanguageInformationInterface|null
     */
    public function getFallbackLanguage($mixID);

    /**
     * Set the current working language for the whole data provider.
     *
     * @param string $strLanguage The new language, use short tag "2 chars like de, fr etc.".
     *
     * @return DataProviderInterface
     */
    public function setCurrentLanguage($strLanguage);

    /**
     * Get the current working language.
     *
     * @return string Short tag for the current working language like de or fr etc.
     */
    public function getCurrentLanguage();
}
