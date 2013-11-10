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

use DcGeneral\Event\EnvironmentAwareEvent;

class GetGroupHeaderEvent
	extends EnvironmentAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-group-header';

	/**
	 * @var string
	 */
	protected $groupField;

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;

	/**
	 * @var int
	 */
	protected $sortingMode;

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @param string $groupField
	 *
	 * @return $this
	 */
	public function setGroupField($groupField)
	{
		$this->groupField = $groupField;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getGroupField()
	{
		return $this->groupField;
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
	 * @param int $sortingMode
	 *
	 * @return $this
	 */
	public function setSortingMode($sortingMode)
	{
		$this->sortingMode = $sortingMode;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getSortingMode()
	{
		return $this->sortingMode;
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}
}
