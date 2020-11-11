<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
 * @SuppressWarnings(PHPMD.LongClassName)
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
     * @param null|string $propertyName      The name of the property to limit execution on.
     *
     * @return void
     */
    public function setRestrictions($dataContainerName = null, $propertyName = null)
    {
        parent::setRestrictions($dataContainerName);
        $this->propertyName = $propertyName;
    }

    // @codingStandardsIgnoreStart
    /**
     * Check the restrictions against the information within the event and determine if the callback shall be executed.
     *
     * @param BuildWidgetEvent|DecodePropertyValueForWidgetEvent|EncodePropertyValueFromWidgetEvent|GetPropertyOptionsEvent $event The Event for which the callback shall be invoked.
     * @codingStandardsIgnoreEnd
     *
     * @return bool
     */
    public function wantToExecute($event)
    {
        if (\method_exists($event, 'getPropertyName')) {
            $property = $event->getPropertyName();
        } elseif ($event->getProperty() instanceof PropertyInterface) {
            $property = $event->getProperty()->getName();
        } else {
            $property = $event->getProperty();
        }

        return parent::wantToExecute($event)
            && (empty($this->propertyName) || ($this->propertyName === $property));
    }
}
