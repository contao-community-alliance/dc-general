<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2016 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @copyright  2013-2016 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber;

use Contao\StringUtil;
use Contao\String;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class convert file sources to uuid and back to sources in rich text editor
 */
class RichTextFileUuidSubscriber implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EncodePropertyValueFromWidgetEvent::NAME => array(
                array('convertFileSourceToUuid')
            ),

            DecodePropertyValueForWidgetEvent::NAME => array(
                array('convertUuidToFileSource')
            )
        );
    }

    /**
     * Convert file source to uuid before save the model.
     * After convert this is an insert tag {{file::uuid}}.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event to handle.
     *
     * @return void
     */
    public function convertFileSourceToUuid(EncodePropertyValueFromWidgetEvent $event)
    {
        $environment          = $event->getEnvironment();
        $dataDefinition       = $environment->getDataDefinition();
        $propertiesDefinition = $dataDefinition->getPropertiesDefinition();
        $property             = $propertiesDefinition->getProperty($event->getProperty());


        if (!array_key_exists('rte', $property->getExtra())
            || strpos($property->getExtra()['rte'], 'tiny') !== 0
        ) {
            return;
        }

        // PHP 7 compatibility, see https://github.com/contao/core-bundle/issues/309
        if (version_compare(VERSION . '.' . BUILD, '3.5.5', '>=')) {
            $value = StringUtil::srcToInsertTag($event->getValue());
        } else {
            $value = String::srcToInsertTag($event->getValue());
        }

        $event->setValue(
            $value
        );
    }

    /**
     * Convert uuid to file source to see the right source in the rich text editor.
     * After convert this back to the original source.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event to handle.
     *
     * @return void
     */
    public function convertUuidToFileSource(DecodePropertyValueForWidgetEvent $event)
    {
        $environment          = $event->getEnvironment();
        $dataDefinition       = $environment->getDataDefinition();
        $propertiesDefinition = $dataDefinition->getPropertiesDefinition();
        $property             = $propertiesDefinition->getProperty($event->getProperty());


        if (!array_key_exists('rte', $property->getExtra())
            || strpos($property->getExtra()['rte'], 'tiny') !== 0
        ) {
            return;
        }

        // PHP 7 compatibility, see https://github.com/contao/core-bundle/issues/309
        if (version_compare(VERSION . '.' . BUILD, '3.5.5', '>=')) {
            $value = StringUtil::insertTagToSrc($event->getValue());
        } else {
            $value = String::insertTagToSrc($event->getValue());
        }

        $event->setValue(
            $value
        );
    }
}
