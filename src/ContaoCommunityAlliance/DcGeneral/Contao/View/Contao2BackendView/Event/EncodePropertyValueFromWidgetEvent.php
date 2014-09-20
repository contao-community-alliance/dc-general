<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class EncodePropertyValueFromWidgetEvent.
 *
 * This event is issued when a property value has to be converted from data understood by the widget into the native
 * data (presented by the data provider).
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class EncodePropertyValueFromWidgetEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.encode-property-value-from-widget';

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
     * The property value bag where values are to be retrieved from.
     *
     * @var PropertyValueBagInterface
     */
    protected $propertyValues;

    /**
     * Create a new model aware event.
     *
     * @param EnvironmentInterface      $environment    The environment.
     *
     * @param ModelInterface            $model          The model attached to the event.
     *
     * @param PropertyValueBagInterface $propertyValues The property value bag the property value originates from.
     */
    public function __construct(
        EnvironmentInterface $environment,
        ModelInterface $model,
        PropertyValueBagInterface $propertyValues
    ) {
        parent::__construct($environment, $model);
        $this->propertyValues = $propertyValues;
    }

    /**
     * Retrieve the property value bag where values are stored.
     *
     * @return PropertyValueBagInterface
     */
    public function getPropertyValueBag()
    {
        return $this->propertyValues;
    }

    /**
     * Set the name of the property.
     *
     * @param string $property The name of the property.
     *
     * @return EncodePropertyValueFromWidgetEvent
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
     * @return EncodePropertyValueFromWidgetEvent
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
