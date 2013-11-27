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

class ManipulateWidgetEvent
	extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.view.contao2backend.manipulate-widget';

	/**
	 * @var \Widget
	 */
	protected $widget;

	/**
	 * @var PropertyInterface
	 */
	protected $property;

	/**
	 * Create a new event.
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @param ModelInterface       $model
	 *
	 * @param PropertyInterface    $property
	 *
	 * @param \Widget              $widget
	 */
	public function __construct(EnvironmentInterface $environment, ModelInterface $model, PropertyInterface $property, \Widget $widget)
	{
		parent::__construct($environment, $model);

		$this->property = $property;
		$this->widget   = $widget;
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
