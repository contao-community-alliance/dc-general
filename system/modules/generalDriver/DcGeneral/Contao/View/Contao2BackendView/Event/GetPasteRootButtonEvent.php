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
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $next;

	/**
	 * @var \DcGeneral\Data\ModelInterface
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
	 * @param \DcGeneral\Data\ModelInterface $next
	 *
	 * @return $this
	 */
	public function setNext($next)
	{
		$this->next = $next;

		return $this;
	}

	/**
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * @param \DcGeneral\Data\ModelInterface $previous
	 *
	 * @return $this
	 */
	public function setPrevious($previous)
	{
		$this->previous = $previous;

		return $this;
	}

	/**
	 * @return \DcGeneral\Data\ModelInterface
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
