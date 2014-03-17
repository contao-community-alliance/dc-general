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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class DecodePropertyValueForWidgetEvent.
 *
 * This event is issued when a property value has to be converted from the native data (presented by the data provider)
 * into data understood by the widget.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class DecodePropertyValueForWidgetEvent
	extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.view.contao2backend.decode-property-value-for-widget';

	/**
	 * The name of the property for which the data shall be decoded.
	 *
	 * @var string
	 */
	protected $property;

	/**
	 * The value of the data.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Set the name of the property.
	 *
	 * @param string $property The name of the property.
	 *
	 * @return DecodePropertyValueForWidgetEvent
	 */
	public function setProperty($property)
	{
		$this->property = $property;

		return $this;
	}

	/**
	 * Retrieve the name of the property.
	 *
	 * @return string
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * Set the value in the event.
	 *
	 * @param mixed $value The new value.
	 *
	 * @return DecodePropertyValueForWidgetEvent
	 */
	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * Retrieve the value from the event.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
}

