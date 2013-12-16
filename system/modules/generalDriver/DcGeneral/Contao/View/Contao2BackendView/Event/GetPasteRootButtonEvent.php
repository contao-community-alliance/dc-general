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
	 * @var bool
	 */
	protected $pasteDisabled;

	/**
	 * @param string $href
	 *
	 * @return $this
	 */
	public function setHref($href)
	{
		$this->href = $href;

		return $this;
	}
	/**
	 * @return string
	 */
	public function getHref()
	{
		return $this->href;
	}

	/**
	 * @param boolean $pasteDisabled
	 *
	 * @return $this
	 */
	public function setPasteDisabled($pasteDisabled)
	{
		$this->pasteDisabled = $pasteDisabled;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isPasteDisabled()
	{
		return $this->pasteDisabled;
	}
}
