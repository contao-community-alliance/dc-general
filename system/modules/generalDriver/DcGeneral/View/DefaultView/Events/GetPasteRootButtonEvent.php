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

namespace DcGeneral\View\DefaultView\Events;

class GetPasteRootButtonEvent
	extends BaseButtonEvent
{
	const NAME = 'DcGeneral\View\DefaultView\Events\GetPasteRootButton';

	/**
	 * @var bool
	 */
	protected $blnCircularReference;

	/**
	 * The href information to use for the paste button.
	 *
	 * @var string
	 */
	protected $href;

	/**
	 * @var string
	 */
	protected $next;

	/**
	 * @var string
	 */
	protected $previous;

	/**
	 * @var bool
	 */
	protected $pasteDisabled;

	/**
	 * @param boolean $blnCircularReference
	 *
	 * @return $this
	 */
	public function setCircularReference($blnCircularReference)
	{
		$this->blnCircularReference = $blnCircularReference;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getCircularReference()
	{
		return $this->blnCircularReference;
	}

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
	 * @param string $strNext
	 *
	 * @return $this
	 */
	public function setNext($strNext)
	{
		$this->next = $strNext;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * @param string $strPrevious
	 *
	 * @return $this
	 */
	public function setPrevious($strPrevious)
	{
		$this->previous = $strPrevious;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPrevious()
	{
		return $this->previous;
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
