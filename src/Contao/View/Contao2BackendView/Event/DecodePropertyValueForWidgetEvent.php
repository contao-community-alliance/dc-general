<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class DecodePropertyValueForWidgetEvent.
 *
 * This event is issued when a property value has to be converted from the native data (presented by the data provider)
 * into data understood by the widget.
 */
class DecodePropertyValueForWidgetEvent extends AbstractModelAwareEvent
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
