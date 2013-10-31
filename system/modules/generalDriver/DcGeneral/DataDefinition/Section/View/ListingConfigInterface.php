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

namespace DcGeneral\DataDefinition\Section\View;

interface ListingConfigInterface
{
    /**
     * Return the item formatter.
     * 
     * @return FormatterInterface
     */
    public function getItemFormatter();
    
    /**
     * Return the item label formatter.
     * 
     * @return FormatterInterface
     */
    public function getItemLabelFormatter();
    
    /**
     * Return the default sorting.
     * 
     * @return array Array of property names as keys and "ASC" and "DESC" as value.
     */
    public function getDefaultSort();
}
