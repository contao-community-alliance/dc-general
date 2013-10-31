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

use DcGeneral\Event\EnvironmentAwareEvent;

class ModelToLabelEvent
	extends EnvironmentAwareEvent
{
    const NAME = 'dc-general.view.widget.model-to-label';

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var \DcGeneral\DataDefinition\ListLabelInterface
	 */
	protected $listLabel;

	/**
	 * @var array
	 */
	protected $args;

	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	public function setArgs($args)
	{
		$this->args = $args;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getArgs()
	{
		return $this->args;
	}

	/**
	 * @param string $label
	 *
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->label = $label;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @param \DcGeneral\DataDefinition\ListLabelInterface $listLabel
	 *
	 * @return $this
	 */
	public function setListLabel($listLabel)
	{
		$this->listLabel = $listLabel;

		return $this;
	}

	/**
	 * @return \DcGeneral\DataDefinition\ListLabelInterface
	 */
	public function getListLabel()
	{
		return $this->listLabel;
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
}
