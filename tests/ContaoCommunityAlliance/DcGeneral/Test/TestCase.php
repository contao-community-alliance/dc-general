<?php

/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

/**
 * Base TestCase class.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function aliasContaoClass($class)
    {
        if (!class_exists($class)) {
            class_alias('\\Contao\\' . $class, $class);
        }
    }
}
