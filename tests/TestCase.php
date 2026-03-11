<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2025 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao\BackendTemplate;
use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao\Config;
use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao\Controller;
use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\Contao\Template;
use ContaoCommunityAlliance\DcGeneral\Test\Fixtures\ContaoTwig;

use function class_alias;
use function class_exists;

/**
 * Base TestCase class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Initialize the contao backend template.
     *
     * @return void
     */
    protected static function initializeContaoBackendTemplate(): void
    {
        if (class_exists(\Contao\BackendTemplate::class, false)) {
            return;
        }

        class_alias(BackendTemplate::class, \Contao\BackendTemplate::class);
        class_alias(BackendTemplate::class, \BackendTemplate::class);
    }

    /**
     * Initialize the contao config.
     */
    protected static function initializeContaoConfig(): void
    {
        if (class_exists(\Contao\Config::class, false)) {
            return;
        }

        class_alias(Config::class, \Contao\Config::class);
        class_alias(Config::class, \Config::class);
    }

    /**
     * Initialize the contao controller.
     */
    protected static function initializeContaoController(): void
    {
        if (class_exists(\Contao\Controller::class, false)) {
            return;
        }

        class_alias(Controller::class, \Contao\Controller::class);
        class_alias(Controller::class, \Controller::class);
    }

    /**
     * Initialize the contao twig.
     */
    protected static function initializeContaoTwig(): void
    {
        if (class_exists(\ContaoTwig::class, false)) {
            return;
        }

        class_alias(ContaoTwig::class, \ContaoTwig::class);
    }

    /**
     * Initialize the contao template.
     */
    protected static function initializeContaoTemplate(): void
    {
        if (class_exists(\Contao\Template::class, false)) {
            return;
        }

        class_alias(Template::class, \Contao\Template::class);
        class_alias(Template::class, \Template::class);
    }
}
