<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;


use Contao\Config;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;

class ConvertTimestampWidgetListener
{

    /**
     * Encode an timestamp attribute value from a widget value.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The subscribed event.
     *
     * @return void
     */
    public function handleEncodePropertyValueFromWidget(EncodePropertyValueFromWidgetEvent $event)
    {
        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $propertyDefinition = $dataDefinition->getPropertiesDefinition()->getProperty($event->getProperty());

        $extra = $propertyDefinition->getExtra();
        $rgxp  = $extra['rgxp'];
        if (!\in_array($rgxp, ['date', 'time', 'datim'])) {
            return;
        }

        $date = \DateTime::createFromFormat($this->getDateTimeFormat($rgxp), $event->getValue());

        if ($date) {
            $event->setValue($date->getTimestamp());
        }
    }

    /**
     * Decode an timestamp attribute value for a widget value.
     *
     * @param DecodePropertyValueForWidgetEvent $event The subscribed event.
     *
     * @return void
     */
    public function handleDecodePropertyValueForWidgetEvent(DecodePropertyValueForWidgetEvent $event)
    {
        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $propertyDefinition = $dataDefinition->getPropertiesDefinition()->getProperty($event->getProperty());

        $extra = $propertyDefinition->getExtra();
        $rgxp  = $extra['rgxp'];
        if (!\in_array($rgxp, ['date', 'time', 'datim'])) {
            return;
        }

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        $value      = $event->getValue();

        if (\is_numeric($value)) {
            $dateEvent = new ParseDateEvent($value, $this->getDateTimeFormat($rgxp));
            $dispatcher->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

            $event->setValue($dateEvent->getResult());
        }
    }

    private function getDateTimeFormat($format)
    {
        return Config::get($format.'Format');
    }
}
