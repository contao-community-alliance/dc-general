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
 * Class GetOperationButtonEvent.
 *
 * This event gets emitted when an operation button is rendered.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetOperationButtonEvent
	extends BaseButtonEvent
{
	const NAME = 'dc-general.view.contao2backend.get-operation-button';

	/**
	 * The command for which the button is being rendered.
	 *
	 * @var \DcGeneral\DataDefinition\Definition\View\CommandInterface
	 */
	protected $command;

	/**
	 * The model to which the command shall be applied to.
	 *
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;

	/**
	 * The ids of any child records of the model.
	 *
	 * @var array
	 */
	protected $childRecordIds;

	/**
	 * Determinator if there is a circular reference from an item in the clipboard to the current model.
	 *
	 * @var bool
	 */
	protected $circularReference;

	/**
	 * The id of the next model succeeding the current model.
	 *
	 * TODO: should this rather be the real model? Might be of more use.
	 *
	 * @var string
	 */
	protected $next;

	/**
	 * The id of the model preceeding the current model.
	 *
	 * TODO: should this rather be the real model? Might be of more use.
	 *
	 * @var string
	 */
	protected $previous;

	/**
	 * The href for the command.
	 *
	 * @var string
	 */
	protected $href;

	/**
	 * Set the attached command.
	 *
	 * @param \DcGeneral\DataDefinition\Definition\View\CommandInterface $objCommand The command.
	 *
	 * @return $this
	 */
	public function setCommand($objCommand)
	{
		$this->command = $objCommand;

		return $this;
	}

	/**
	 * Retrieve the attached command.
	 *
	 * @return \DcGeneral\DataDefinition\Definition\View\CommandInterface
	 */
	public function getCommand()
	{
		return $this->command;
	}

	/**
	 * Set the ids of the child records of the current model.
	 *
	 * @param array $childRecordIds The list of ids.
	 *
	 * @return $this
	 */
	public function setChildRecordIds($childRecordIds)
	{
		$this->childRecordIds = $childRecordIds;

		return $this;
	}

	/**
	 * Retrieve the ids of the child records of the current model.
	 *
	 * @return array
	 */
	public function getChildRecordIds()
	{
		return $this->childRecordIds;
	}

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
		$this->circularReference = $circularReference;

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
		return $this->circularReference;
	}

	/**
	 * Set the id of the next model in the list, succeeding the current model.
	 *
	 * @param string $next The id of the successor.
	 *
	 * @return $this
	 */
	public function setNext($next)
	{
		$this->next = $next;

		return $this;
	}

	/**
	 * Get the id of the next model in the list, succeeding the current model.
	 *
	 * @return string
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * Set the id of the previous model in the list, preceding the current model.
	 *
	 * @param string $previous The id of the predecessor.
	 *
	 * @return $this
	 */
	public function setPrevious($previous)
	{
		$this->previous = $previous;

		return $this;
	}

	/**
	 * Get the id of the previous model in the list, preceding the current model.
	 *
	 * @return string
	 */
	public function getPrevious()
	{
		return $this->previous;
	}

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
	 * Retrieve the href for the button.
	 *
	 * @return string
	 */
	public function getHref()
	{
		return $this->href;
	}

	/**
	 * Set the model currently in scope.
	 *
	 * @param \DcGeneral\Data\ModelInterface $model The model.
	 *
	 * @return $this
	 */
	public function setObjModel($model)
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
}
