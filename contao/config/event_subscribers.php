<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\Contao\Event\Subscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller\ClipboardController;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Filter\LanguageFilter;

return array(
    new Subscriber(),
    new ClipboardController(),
    new LanguageFilter(),
);
