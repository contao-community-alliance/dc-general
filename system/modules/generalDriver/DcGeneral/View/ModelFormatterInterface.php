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

namespace DcGeneral\View;

use DcGeneral\Data\ModelInterface;

/**
 * The model formatter format a model and create a string representation.
 */
interface ModelFormatterInterface
{
    /**
     * Format a model and return a string representation.
     * 
     * @return string
     */
    public function format(ModelInterface $model);
}
