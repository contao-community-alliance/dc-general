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

namespace DcGeneral\View\BackendView\Event;

use DcGeneral\Event\EnvironmentAwareEvent;

class BaseGetButtonsEvent
	extends EnvironmentAwareEvent
{
    const NAME = 'dc-general.view.default.get-buttons';

	/**
	 * @var string[]
	 */
	protected $buttons;

	/**
	 * @param \string[] $buttons
	 *
	 * @return $this
	 */
	public function setButtons($buttons)
	{
		$this->buttons = $buttons;

		return $this;
	}

	/**
	 * @return \string[]
	 */
	public function getButtons()
	{
		return $this->buttons;
	}
}
