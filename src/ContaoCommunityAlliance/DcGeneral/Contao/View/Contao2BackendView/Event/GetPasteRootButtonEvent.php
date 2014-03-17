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

/**
 * Class GetPasteRootButtonEvent.
 *
 * This event gets emitted when a root button get's rendered in hierarchical mode.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetPasteRootButtonEvent
	extends BaseButtonEvent
{
	const NAME = 'dc-general.view.contao2backend.get-paste-root-button';

	/**
	 * The href information to use for the paste button.
	 *
	 * @var string
	 */
	protected $href;

	/**
	 * Determinator if the paste button shall be disabled.
	 *
	 * @var bool
	 */
	protected $pasteDisabled;

	/**
	 * Set the href for the button.
	 *
	 * @param string $href The href.
	 *
	 * @return $this
	 */
	public function setHref($href)
	{
		$this->href = $href;

		return $this;
	}
	/**
	 * Get the href for the button.
	 *
	 * @return string
	 */
	public function getHref()
	{
		return $this->href;
	}

	/**
	 * Set the determinator if the button shall be disabled or not.
	 *
	 * @param boolean $pasteDisabled The flag.
	 *
	 * @return $this
	 */
	public function setPasteDisabled($pasteDisabled)
	{
		$this->pasteDisabled = $pasteDisabled;

		return $this;
	}

	/**
	 * Check if the paste button shall be disabled or not.
	 *
	 * @return boolean
	 */
	public function isPasteDisabled()
	{
		return $this->pasteDisabled;
	}
}
