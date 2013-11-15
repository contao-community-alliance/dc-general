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

use DcGeneral\Event\AbstractModelAwareEvent;

class FormatGroupLabelEvent
	extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.view.contao2backend.format-group-event';

	/**
	 * @var string
	 */
	protected $groupLabel;

	/**
	 * @var string
	 */
	protected $propertyName;

	/**
	 * @var string
	 */
	protected $mode;

	public function __construct(EnvironmentInterface $environment, ModelInterface $model, $propertyName, $mode)
	{
		parent::__construct($environment, $model);
		$this->propertyName = (string) $propertyName;
		$this->mode = $mode;
	}

	/**
	 * @param string $group
	 */
	public function setGroupLabel($group)
	{
		$this->groupLabel = $group;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getGroupLabel()
	{
		return $this->groupLabel;
	}

	/**
	 * @return string
	 */
	public function getPropertyName()
	{
		return $this->propertyName;
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}
}
