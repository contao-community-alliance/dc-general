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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

/**
 * Class GetGlobalButtonsEvent.
 *
 * This event gets issued when all global buttons have been generated and holds the list of the rendered buttons.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetGlobalButtonsEvent extends BaseGetButtonsEvent
{
    const NAME = 'dc-general.view.contao2backend.get-global-buttons';
}
