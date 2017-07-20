<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2016 Contao Community Alliance.
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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2016 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\Contao\Event\Subscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\Subscriber\FallbackResetSubscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller\ClipboardController;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Filter\LanguageFilter;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\CheckPermission;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\RichTextFileUuidSubscriber;

if ('BE' === TL_MODE) {
    return array(
        new Subscriber(),
        new ClipboardController(),
        new LanguageFilter(),
        new FallbackResetSubscriber(),
        new RichTextFileUuidSubscriber(),
        new CheckPermission()
    );
}

return array(
    new FallbackResetSubscriber(),
);
