<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Interface MultiLanguageDataProviderInterface.
 *
 * This interface describes how to interface with a multi language data provider.
 *
 * @package DcGeneral\Data
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
