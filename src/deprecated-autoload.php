<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

// This hack is to load the "old locations" of the classes.
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener\ColorPickerWizardListener;
use ContaoCommunityAlliance\DcGeneral\Exception\DefinitionException;
use ContaoCommunityAlliance\DcGeneral\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotCreatableException;
use ContaoCommunityAlliance\DcGeneral\Exception\NotDeletableException;

spl_autoload_register(
    function ($class) {
        static $classes = [
            // @codingStandardsIgnoreStart Line exceeds 120 characters
            '\ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\DefinitionException'          => DefinitionException::class,
            '\ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException'        => EditOnlyModeException::class,
            '\ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotCreatableException'        => NotCreatableException::class,
            '\ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeletableException'        => NotDeletableException::class,
            '\ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\ColorPickerWizardSubscriber' => ColorPickerWizardListener::class,
            // @codingStandardsIgnoreEnd
        ];

        if (isset($classes[$class])) {
            // @codingStandardsIgnoreStart Silencing errors is discouraged
            @trigger_error('Class "' . $class . '" has been renamed to "' . $classes[$class] . '"', E_USER_DEPRECATED);
            // @codingStandardsIgnoreEnd

            if (!class_exists($classes[$class])) {
                spl_autoload_call($class);
            }

            class_alias($classes[$class], $class);
        }
    }
);
