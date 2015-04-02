<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;

/**
 * Class GetPasteButtonEvent.
 *
 * This event gets emitted when a paste button is generated.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetPasteButtonEvent extends BaseButtonEvent
{
    const NAME = 'dc-general.view.contao2backend.get-paste-button';

    /**
     * Determinator if there is a circular reference from an item in the clipboard to the current model.
     *
     * @var bool
     */
    protected $circularReference;

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
     * @var ModelInterface
     */
    protected $model;

    /**
     * The next model in the list.
     *
     * @var ModelInterface
     */
    protected $next;

    /**
     * The previous model in the list.
     *
     * @var ModelInterface
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
     * The models currently in the clipboard.
     *
     * @var CollectionInterface
     */
    protected $containedModels;

    /**
     * Set determinator if there exists a circular reference.
     *
     * This flag determines if there exists a circular reference between the item currently in the clipboard and the
     * current model. A circular reference is of relevance when performing a cut and paste operation for example.
     *
     * @param boolean $circularReference The flag.
     *
     * @return $this
     */
    public function setCircularReference($circularReference)
    {
        $this->circularReference = (bool) $circularReference;

        return $this;
    }

    /**
     * Get determinator if there exists a circular reference.
     *
     * This flag determines if there exists a circular reference between the item currently in the clipboard and the
     * current model. A circular reference is of relevance when performing a cut and paste operation for example.
     *
     * @return boolean
     *
     * @deprecated Use isCircularReference().
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCircularReference()
    {
        return $this->isCircularReference();
    }

    /**
     * Get determinator if there exists a circular reference.
     *
     * This flag determines if there exists a circular reference between the item currently in the clipboard and the
     * current model. A circular reference is of relevance when performing a cut and paste operation for example.
     *
     * @return boolean
     */
    public function isCircularReference()
    {
        return $this->circularReference;
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
     * @param ModelInterface $model The model currently in scope.
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
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the next model.
     *
     * @param ModelInterface $next The next model.
     *
     * @return $this
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get the next model.
     *
     * @return ModelInterface
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set the previous model.
     *
     * @param ModelInterface $previous The previous model.
     *
     * @return $this
     */
    public function setPrevious($previous)
    {
        $this->previous = $previous;

        return $this;
    }

    /**
     * Get the previous model.
     *
     * @return ModelInterface
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

    /**
     * Retrieve the collection of contained models.
     *
     * @return CollectionInterface
     */
    public function getContainedModels()
    {
        return $this->containedModels;
    }

    /**
     * Set the collection of contained models.
     *
     * @param CollectionInterface $containedModels The collection of contained models.
     *
     * @return GetPasteButtonEvent
     */
    public function setContainedModels($containedModels)
    {
        $this->containedModels = $containedModels;

        return $this;
    }
}
