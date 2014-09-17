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
 * Class GetEditModeButtonsEvent.
 *
 * This event gets issued to retrieve the buttons to be displayed in when in edit mode on the very bottom.
 *
 * These buttons include, but are not limited to, save, save and close, save and back, ...
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetEditModeButtonsEvent extends BaseGetButtonsEvent
{
    const NAME = 'dc-general.view.contao2backend.get-edit-mode-buttons';
}
