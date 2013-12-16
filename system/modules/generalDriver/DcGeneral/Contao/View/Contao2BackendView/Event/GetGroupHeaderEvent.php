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

class GetGroupHeaderEvent
	extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-group-header';

	/**
	 * @var string
	 */
	protected $groupField;

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $groupingMode;

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @param string $groupField
	 *
	 * @return $this
	 */
	public function __construct(
		EnvironmentInterface $environment,
		ModelInterface $model,
		$propertyName,
		$propertyValue,
		$groupingMode
	)
	{
		parent::__construct($environment, $model);

		$this->groupField   = $propertyName;
		$this->value        = $propertyValue;
		$this->groupingMode = $groupingMode;
	}

	/**
	 * @return string
	 */
	public function getGroupField()
	{
		return $this->groupField;
	}

	/**
	 * Get the grouping mode in use as defined in the listing config.
	 *
	 * @return string
	 *
	 * @see    ListingConfigInterface
	 */
	public function getGroupingMode()
	{
		return $this->groupingMode;
	}

	/**
	 * Set the value to use in the group header.
	 *
	 * @param string $value The value.
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
