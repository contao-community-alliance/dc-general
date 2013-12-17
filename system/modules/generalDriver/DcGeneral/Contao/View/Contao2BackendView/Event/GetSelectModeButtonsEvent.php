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

namespace DcGeneral\Contao\View\Contao2BackendView\Event;

/**
 * Class GetSelectModeButtonsEvent.
 *
 * This event gets emitted when the buttons for the select mode get fetched.
 * These buttons include "edit multiple", "override" etc.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetSelectModeButtonsEvent
	extends BaseGetButtonsEvent
{
	const NAME = 'dc-general.view.contao2backend.get-select-mode-buttons';
}
