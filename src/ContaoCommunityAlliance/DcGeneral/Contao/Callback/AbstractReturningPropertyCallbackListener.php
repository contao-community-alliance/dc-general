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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;

/**
 * Class AbstractReturningPropertyCallbackListener.
 *
 * Abstract base class for a callback listener.
 *
 * @package DcGeneral\Contao\Callback
 */
abstract class AbstractReturningPropertyCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * The name of the property to limit execution on.
     *
     * @var null|string
     */
    protected $propertyName;

    /**
     * Set the restrictions for this callback.
     *
     * @param null|string $dataContainerName The name of the data container to limit execution on.
     *
     * @param null|string $propertyName      The name of the property to limit execution on.
     *
     * @return void
     */
    public function setRestrictions($dataContainerName = null, $propertyName = null)
    {
        parent::setRestrictions($dataContainerName);
        $this->propertyName = $propertyName;
    }

    /**
     * Check the restrictions against the information within the event and determine if the callback shall be executed.
     *
     * @param BuildWidgetEvent|DecodePropertyValueForWidgetEvent|EncodePropertyValueFromWidgetEvent|GetPropertyOptionsEvent $event The Event for which the callback shall be invoked.
     *
     * @return bool
     */
    public function wantToExecute($event)
    {
        if (method_exists($event, 'getPropertyName')) {
            $property = $event->getPropertyName();
        } else {

            if ($event->getProperty() instanceof PropertyInterface) {
                $property = $event->getProperty()->getName();
            } else {
                $property = $event->getProperty();
            }
        }

        return parent::wantToExecute($event)
            && (empty($this->propertyName) || ($property == $this->propertyName));
    }
}
