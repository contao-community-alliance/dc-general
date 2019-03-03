<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Class MultiLanguageDataProvider.
 * Implementation of a multi language Contao database data provider.
 *
 * The default language will be initialized to "en".
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
    }

    /**
     * Get all available languages of a certain record.
     *
     * @param mixed $mixID The ID of the record to retrieve.
     *
     * @return LanguageInformationCollectionInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getLanguages($mixID)
    {
        $collection = new DefaultLanguageInformationCollection();

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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFallbackLanguage($mixID)
    {
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
