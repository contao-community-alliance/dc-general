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
 * Class GetPasteButtonEvent.
 *
 * This event gets emitted when a paste button is generated.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetPasteButtonEvent
	extends BaseButtonEvent
{
	const NAME = 'dc-general.view.contao2backend.get-paste-button';

	/**
	 * Determinator if there is a circular reference from an item in the clipboard to the current model.
	 *
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
	 * The model to which the command shall be applied to.
	 *
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;

	/**
	 * Id of the next model in the list.
	 *
	 * @var string
	 */
	protected $next;

	/**
	 * Id of the previous model in the list.
	 *
	 * @var string
	 */
	protected $previous;

	/**
	 * Determinator if the paste into button shall be disabled.
	 *
	 * @var bool
	 */
	protected $pasteIntoDisabled;

	/**
	 * Determinator if the paste after button shall be disabled.
	 *
	 * @var bool
	 */
	protected $pasteAfterDisabled;

	/**
	 * Set determinator if there exists a circular reference.
	 *
	 * This flag determines if there exists a circular reference between the item currently in the clipboard and the
	 * current model. A circular reference is of relevance when performing a cut and paste operation for example.
	 *
	 * @param boolean $blnCircularReference The flag.
	 *
	 * @return $this
	 */
	public function setCircularReference($blnCircularReference)
	{
		$this->blnCircularReference = $blnCircularReference;

		return $this;
	}

	/**
	 * Get determinator if there exists a circular reference.
	 *
	 * This flag determines if there exists a circular reference between the item currently in the clipboard and the
	 * current model. A circular reference is of relevance when performing a cut and paste operation for example.
	 *
	 * @return boolean
	 */
	public function getCircularReference()
	{
		return $this->blnCircularReference;
	}

	/**
	 * Set the href for the paste after button.
	 *
	 * @param string $hrefAfter The href.
	 *
	 * @return $this
	 */
	public function setHrefAfter($hrefAfter)
	{
		$this->hrefAfter = $hrefAfter;

		return $this;
	}
	/**
	 * Get the href for the paste after button.
	 *
	 * @return string
	 */
	public function getHrefAfter()
	{
		return $this->hrefAfter;
	}

	/**
	 * Set the href for the paste into button.
	 *
	 * @param string $hrefInto The href.
	 *
	 * @return $this
	 */
	public function setHrefInto($hrefInto)
	{
		$this->hrefInto = $hrefInto;

		return $this;
	}

	/**
	 * Get the href for the paste into button.
	 *
	 * @return string
	 */
	public function getHrefInto()
	{
		return $this->hrefInto;
	}

	/**
	 * Set the html code for the paste after button.
	 *
	 * @param string $html The HTML code.
	 *
	 * @return $this
	 */
	public function setHtmlPasteAfter($html)
	{
		$this->htmlPasteAfter = $html;

		return $this;
	}

	/**
	 * Get the html code for the paste after button.
	 *
	 * @return string
	 */
	public function getHtmlPasteAfter()
	{
		return $this->htmlPasteAfter;
	}

	/**
	 * Set the html code for the paste into button.
	 *
	 * @param string $html The HTML code.
	 *
	 * @return $this
	 */
	public function setHtmlPasteInto($html)
	{
		$this->htmlPasteInto = $html;

		return $this;
	}

	/**
	 * Get the html code for the paste after button.
	 *
	 * @return string
	 */
	public function getHtmlPasteInto()
	{
		return $this->htmlPasteInto;
	}

	/**
	 * Set the model currently in scope.
	 *
	 * @param \DcGeneral\Data\ModelInterface $model The model currently in scope.
	 *
	 * @return $this
	 */
	public function setModel($model)
	{
		$this->model = $model;

		return $this;
	}

	/**
	 * Get the model currently in scope.
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Set the id of the next model.
	 *
	 * @param string $strNext The id of the next model.
	 *
	 * @return $this
	 */
	public function setNext($strNext)
	{
		$this->next = $strNext;

		return $this;
	}

	/**
	 * Get the id of the next model.
	 *
	 * @return string
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * Set the id of the previous model.
	 *
	 * @param string $strPrevious The id of the previous model.
	 *
	 * @return $this
	 */
	public function setPrevious($strPrevious)
	{
		$this->previous = $strPrevious;

		return $this;
	}

	/**
	 * Get the id of the previous model.
	 *
	 * @return string
	 */
	public function getPrevious()
	{
		return $this->previous;
	}

	/**
	 * Set the determinator if the paste after button shall be disabled.
	 *
	 * @param boolean $pasteAfterDisabled Determinator flag for the disabling state.
	 *
	 * @return $this
	 */
	public function setPasteAfterDisabled($pasteAfterDisabled)
	{
		$this->pasteAfterDisabled = $pasteAfterDisabled;

		return $this;
	}

	/**
	 * Check if the paste after button shall be disabled.
	 *
	 * @return boolean
	 */
	public function isPasteAfterDisabled()
	{
		return $this->pasteAfterDisabled;
	}

	/**
	 * Set the determinator if the paste into button shall be disabled.
	 *
	 * @param boolean $pasteIntoDisabled Determinator flag for the disabling state.
	 *
	 * @return $this
	 */
	public function setPasteIntoDisabled($pasteIntoDisabled)
	{
		$this->pasteIntoDisabled = $pasteIntoDisabled;

		return $this;
	}

	/**
	 * Check if the paste into button shall be disabled.
	 *
	 * @return boolean
	 */
	public function isPasteIntoDisabled()
	{
		return $this->pasteIntoDisabled;
	}
}
