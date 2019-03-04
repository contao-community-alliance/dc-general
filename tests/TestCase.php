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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao\BackendTemplate;
use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao\Config;
use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao\Controller;
use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao\Template;
use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\ContaoTwig;

/**
 * Base TestCase class.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function aliasContaoClass($class)
    {
        if (\class_exists($class) && !\class_exists('\\Contao\\' . $class, false)) {
            \class_alias($class, '\\Contao\\' . $class);
        }

        if (\class_exists('\\Contao\\' . $class) && !\class_exists($class, false)) {
            \class_alias('\\Contao\\' . $class, $class);
        }
    }

    /**
     * Initialize the contao backend template.
     *
     * @return void
     */
    protected static function initializeContaoBackendTemplate()
    {
        if (class_exists(\Contao\BackendTemplate::class, false)) {
            return;
        }

        class_alias(BackendTemplate::class, \Contao\BackendTemplate::class);
        class_alias(BackendTemplate::class, \BackendTemplate::class);
    }

    /**
     * Initialize the contao config.
     *
     * @return void
     */
    protected static function initializeContaoConfig()
    {
        if (class_exists(\Contao\Config::class, false)) {
            return;
        }

        class_alias(Config::class, \Contao\Config::class);
        class_alias(Config::class, \Config::class);
    }

    /**
     * Initialize the contao controller.
     *
     * @return void
     */
    protected static function initializeContaoController()
    {
        if (class_exists(\Contao\Controller::class, false)) {
            return;
        }

        class_alias(Controller::class, \Contao\Controller::class);
        class_alias(Controller::class, \Controller::class);
    }

    /**
     * Initialize the contao twig.
     *
     * @return void
     */
    protected static function initializeContaoTwig()
    {
        if (class_exists(\ContaoTwig::class, false)) {
            return;
        }

        class_alias(ContaoTwig::class, \ContaoTwig::class);
    }

    /**
     * Initialize the contao template.
     *
     * @return void
     */
    protected static function initializeContaoTemplate()
    {
        if (class_exists(\Contao\Template::class, false)) {
            return;
        }

        class_alias(Template::class, \Contao\Template::class);
        class_alias(Template::class, \Template::class);
    }
}
