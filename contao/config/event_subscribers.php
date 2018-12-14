<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\Contao\Event\Subscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\Subscriber\DynamicParentTableSubscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\Subscriber\FallbackResetSubscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller\ClipboardController;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Filter\LanguageFilter;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\CheckPermission;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\EditAllHandlerSubscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\RichTextFileUuidSubscriber;

if ('BE' === TL_MODE) {
    return [
        new Subscriber(),
        new ClipboardController(),
        new LanguageFilter(),
        new FallbackResetSubscriber(),
        new RichTextFileUuidSubscriber(),
        new CheckPermission(),
        new DynamicParentTableSubscriber(),
        new EditAllHandlerSubscriber()
    ];
}

return [
    new FallbackResetSubscriber()
];
