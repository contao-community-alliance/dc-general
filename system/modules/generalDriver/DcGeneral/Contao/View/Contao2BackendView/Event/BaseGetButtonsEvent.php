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

use DcGeneral\Event\AbstractEnvironmentAwareEvent;

class BaseGetButtonsEvent
	extends AbstractEnvironmentAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-buttons';

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
