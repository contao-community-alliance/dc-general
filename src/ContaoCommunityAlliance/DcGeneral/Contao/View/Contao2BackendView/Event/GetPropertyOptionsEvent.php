<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class GetPropertyOptionsEvent.
 *
 * This event gets emitted when the options for a property shall get retrieved for the edit view.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class GetPropertyOptionsEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.get-property-options';

    /**
     * The name of the property to retrieve the options for.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * The options for the properties.
     *
     * @var array
     */
    protected $options;

    /**
     * Set the property name to retrieve the options for.
     *
     * @param string $propertyName The name of the property.
     *
     * @return $this
     *
     * @deprecated this method has been renamed to setPropertyName.
     */
    public function setFieldName($propertyName)
    {
        return $this->setPropertyName($propertyName);
    }

    /**
     * Get the property name to retrieve the options for.
     *
     * @return string
     *
     * @deprecated this method has been renamed to getPropertyName.
     */
    public function getFieldName()
    {
        return $this->getPropertyName();
    }

    /**
     * Set the property name to retrieve the options for.
     *
     * @param string $propertyName The name of the property.
     *
     * @return $this
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;

        return $this;
    }

    /**
     * Get the property name to retrieve the options for.
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Set the options for the property in the event.
     *
     * @param array $options The options.
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Retrieve the options for the property from the event.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
