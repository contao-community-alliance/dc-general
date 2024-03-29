<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class convert file sources to uuid and back to sources in rich text editor
 */
class RichTextFileUuidSubscriber implements EventSubscriberInterface
{
    /**
     * The request mode determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * ClipboardController constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

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
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents()
    {
        return [
            EncodePropertyValueFromWidgetEvent::NAME => [
                ['convertFileSourceToUuid']
            ],

            DecodePropertyValueForWidgetEvent::NAME => [
                ['convertUuidToFileSource']
            ]
        ];
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
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $definition = $event->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $propertiesDefinition = $definition->getPropertiesDefinition();
        $property             = $propertiesDefinition->getProperty($event->getProperty());


        if (!\array_key_exists('rte', $property->getExtra()) || (0 !== \strpos($property->getExtra()['rte'], 'tiny'))) {
            return;
        }

        $event->setValue(
            StringUtil::srcToInsertTag($event->getValue())
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
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $definition = $event->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $propertiesDefinition = $definition->getPropertiesDefinition();
        $property             = $propertiesDefinition->getProperty($event->getProperty());


        if (!\array_key_exists('rte', $property->getExtra()) || \strpos($property->getExtra()['rte'], 'tiny') !== 0) {
            return;
        }

        $event->setValue(
            StringUtil::insertTagToSrc($event->getValue())
        );
    }
}
