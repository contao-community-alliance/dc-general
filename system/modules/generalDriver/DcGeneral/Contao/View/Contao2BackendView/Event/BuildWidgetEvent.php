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

use DcGeneral\Data\ModelInterface;
use DcGeneral\DataDefinition\Definition\Palette\PropertyInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Event\AbstractModelAwareEvent;

class BuildWidgetEvent
	extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.view.contao2backend.build-widget';

	/**
	 * @var PropertyInterface
	 */
	protected $property;
	/**
	 * @var \Widget
	 */
	protected $widget;

	/**
	 * Create a new event.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @param ModelInterface       $model
	 *
	 * @param PropertyInterface    $property
	 */
	public function __construct(EnvironmentInterface $environment, ModelInterface $model, PropertyInterface $property)
	{
		parent::__construct($environment, $model);

		$this->property = $property;
	}

	/**
	 * @param \Widget $widget
	 *
	 * @return BuildWidgetEvent
	 */
	public function setWidget($widget)
	{
		$this->widget = $widget;

		return $this;
	}

	/**
	 * @return \Widget
	 */
	public function getWidget()
	{
		return $this->widget;
	}

	/**
	 * @return PropertyInterface
	 */
	public function getProperty()
	{
		return $this->property;
	}
}
