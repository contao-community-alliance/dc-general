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

class GetPasteButtonEvent
	extends BaseButtonEvent
{
    const NAME = 'dc-general.view.widget.get-paste-button';

	/**
	 * @var bool
	 */
	protected $blnCircularReference;

	/**
	 * The href information to use for the paste after button.
	 *
	 * @var string
	 */
	protected $hrefAfter;

	/**
	 * The href information to use for the paste into button.
	 *
	 * @var string
	 */
	protected $hrefInto;

	/**
	 * The Html code to use for the "paste after" button.
	 *
	 * @var string
	 */
	protected $htmlPasteAfter;

	/**
	 * The Html code to use for the "paste into" button.
	 *
	 * @var string
	 */
	protected $htmlPasteInto;

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;

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
	protected $pasteIntoDisabled;

	/**
	 * @var bool
	 */
	protected $pasteAfterDisabled;

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
	 * @param string $hrefAfter
	 *
	 * @return $this
	 */
	public function setHrefAfter($hrefAfter)
	{
		$this->hrefAfter = $hrefAfter;

		return $this;
	}
	/**
	 * @return string
	 */
	public function getHrefAfter()
	{
		return $this->hrefAfter;
	}

	/**
	 * @param string $hrefInto
	 *
	 * @return $this
	 */
	public function setHrefInto($hrefInto)
	{
		$this->hrefInto = $hrefInto;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHrefInto()
	{
		return $this->hrefInto;
	}

	/**
	 * @param string $html
	 *
	 * @return $this
	 */
	public function setHtmlPasteAfter($html)
	{
		$this->htmlPasteAfter = $html;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHtmlPasteAfter()
	{
		return $this->htmlPasteAfter;
	}

	/**
	 * @param string $html
	 *
	 * @return $this
	 */
	public function setHtmlPasteInto($html)
	{
		$this->htmlPasteInto = $html;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHtmlPasteInto()
	{
		return $this->htmlPasteInto;
	}

	/**
	 * @param \DcGeneral\Data\ModelInterface $model
	 *
	 * @return $this
	 */
	public function setModel($model)
	{
		$this->model = $model;

		return $this;
	}

	/**
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getModel()
	{
		return $this->model;
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
	 * @param boolean $pasteAfterDisabled
	 *
	 * @return $this
	 */
	public function setPasteAfterDisabled($pasteAfterDisabled)
	{
		$this->pasteAfterDisabled = $pasteAfterDisabled;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isPasteAfterDisabled()
	{
		return $this->pasteAfterDisabled;
	}

	/**
	 * @param boolean $pasteIntoDisabled
	 *
	 * @return $this
	 */
	public function setPasteIntoDisabled($pasteIntoDisabled)
	{
		$this->pasteIntoDisabled = $pasteIntoDisabled;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isPasteIntoDisabled()
	{
		return $this->pasteIntoDisabled;
	}
}
