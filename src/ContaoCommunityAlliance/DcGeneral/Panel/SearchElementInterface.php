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

namespace ContaoCommunityAlliance\DcGeneral\Panel;

/**
 * This interface describes a search panel element.
 *
 * @package DcGeneral\Panel
 */
interface SearchElementInterface extends PanelElementInterface
{
    /**
     * Add a property that can be searched.
     *
     * @param string $strProperty The property to allow to search on.
     *
     * @return SearchElementInterface
     */
    public function addProperty($strProperty);

    /**
     * Retrieve the list of properties to allow search on.
     *
     * @return string[]
     */
    public function getPropertyNames();

    /**
     * This activates a property for search.
     *
     * @param string $strProperty The property to activate search on.
     *
     * @return SearchElementInterface
     */
    public function setSelectedProperty($strProperty = '');

    /**
     * Retrieves the property currently defined to be searched on.
     *
     * @return string
     */
    public function getSelectedProperty();

    /**
     * Set the value to search for.
     *
     * @param string $mixValue The value to search for.
     *
     * @return SearchElementInterface
     */
    public function setValue($mixValue = null);

    /**
     * Retrieve the value to be searched for.
     *
     * @return string
     */
    public function getValue();
}
