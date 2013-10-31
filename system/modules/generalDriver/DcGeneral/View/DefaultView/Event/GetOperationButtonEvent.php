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

namespace DcGeneral\View\DefaultView\Event;

class GetOperationButtonEvent
	extends BaseButtonEvent
{
	const NAME = 'DcGeneral\View\DefaultView\Event\GetOperationButton';

	/**
	 * @var \DcGeneral\DataDefinition\OperationInterface
	 */
	protected $operation;

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;

	/**
	 * @var array
	 */
	protected $childRecordIds;

	/**
	 * @var bool
	 */
	protected $circularReference;

	/**
	 * @var string
	 */
	protected $next;

	/**
	 * @var string
	 */
	protected $previous;

	/**
	 * @var string
	 */
	protected $href;

	/**
	 * @param \DcGeneral\DataDefinition\OperationInterface $objOperation
	 *
	 * @return $this
	 */
	public function setObjOperation($objOperation)
	{
		$this->objOperation = $objOperation;

		return $this;
	}

	/**
	 * @return \DcGeneral\DataDefinition\OperationInterface
	 */
	public function getObjOperation()
	{
		return $this->operation;
	}

	/**
	 * @param array $childRecordIds
	 *
	 * @return $this
	 */
	public function setChildRecordIds($childRecordIds)
	{
		$this->childRecordIds = $childRecordIds;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getChildRecordIds()
	{
		return $this->childRecordIds;
	}

	/**
	 * @param boolean $circularReference
	 *
	 * @return $this
	 */
	public function setCircularReference($circularReference)
	{
		$this->circularReference = $circularReference;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getCircularReference()
	{
		return $this->circularReference;
	}

	/**
	 * @param string $next
	 *
	 * @return $this
	 */
	public function setNext($next)
	{
		$this->next = $next;

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
	 * @param string $previous
	 *
	 * @return $this
	 */
	public function setPrevious($previous)
	{
		$this->previous = $previous;

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
	 * @param \DcGeneral\Data\ModelInterface $model
	 *
	 * @return $this
	 */
	public function setObjModel($model)
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
}
