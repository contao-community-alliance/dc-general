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

namespace DcGeneral\View\Event;

use DcGeneral\Data\ModelInterface;
use DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Event\AbstractModelAwareEvent;

class RenderReadablePropertyValueEvent
	extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.view.contao2backend.render-readable-property-value';

	/**
	 * @var PropertyInterface
	 */
	protected $property;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var string|null
	 */
	protected $rendered = null;

	function __construct(EnvironmentInterface $environment, ModelInterface $model, PropertyInterface $property, $value)
	{
		parent::__construct($environment, $model);
		$this->property = $property;
		$this->value = $value;
	}

	/**
	 * @return PropertyInterface
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param null|string $rendered
	 *
	 * @return RenderReadablePropertyValueEvent
	 */
	public function setRendered($rendered)
	{
		$this->rendered = $rendered;
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getRendered()
	{
		return $this->rendered;
	}
}
