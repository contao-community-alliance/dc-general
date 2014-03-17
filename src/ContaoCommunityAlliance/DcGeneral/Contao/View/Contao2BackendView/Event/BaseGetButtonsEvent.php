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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * Class BaseGetButtonsEvent.
 *
 * Base event for retrieving buttons. This event is not being emitted anywhere as it is only a base class for other
 * events.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class BaseGetButtonsEvent
	extends AbstractEnvironmentAwareEvent
{
	/**
	 * The name of the event.
	 */
	const NAME = 'dc-general.view.contao2backend.get-buttons';

	/**
	 * The list of buttons.
	 *
	 * @var string[]
	 */
	protected $buttons;

	/**
	 * Set the list of buttons.
	 *
	 * @param string[] $buttons The buttons to be returned.
	 *
	 * @return $this
	 */
	public function setButtons($buttons)
	{
		$this->buttons = $buttons;

		return $this;
	}

	/**
	 * Get the list of buttons.
	 *
	 * @return string[]
	 */
	public function getButtons()
	{
		return $this->buttons;
	}
}
