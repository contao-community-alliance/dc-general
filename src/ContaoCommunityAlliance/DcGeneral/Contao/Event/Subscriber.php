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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Event;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\Twig\DcGeneralExtension;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ResolveWidgetErrorMessageEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Subscriber - gateway to the legacy Contao HOOK style callbacks.
 *
 * @package DcGeneral\Event
 */
class Subscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array
        (
            ResolveWidgetErrorMessageEvent::NAME   => array('resolveWidgetErrorMessage', -1),
            RenderReadablePropertyValueEvent::NAME => 'renderReadablePropertyValue',
            'contao-twig.init'                     => 'initTwig',
        );
    }

    /**
     * Resolve a widget error message.
     *
     * @param ResolveWidgetErrorMessageEvent $event The event being processed.
     *
     * @return void
     */
    public function resolveWidgetErrorMessage(ResolveWidgetErrorMessageEvent $event)
    {
        $error = $event->getError();

        if ($error instanceof \Exception) {
            $event->setError($error->getMessage());
        } elseif (is_object($error)) {
            if (method_exists($error, '__toString')) {
                $event->setError((string)$error);
            } else {
                $event->setError(sprintf('[%s]', get_class($error)));
            }
        } elseif (!is_string($error)) {
            $event->setError(sprintf('[%s]', gettype($error)));
        }
    }

    /**
     * Fetch the options for a certain property.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @param ModelInterface       $model       The model.
     *
     * @param PropertyInterface    $property    The property.
     *
     * @return array
     */
    protected function getOptions($environment, $model, $property)
    {
        $options = $property->getOptions();
        $event   = new GetPropertyOptionsEvent($environment, $model);
        $event->setPropertyName($property->getName());
        $event->setOptions($options);
        $environment->getEventPropagator()->propagate(
            $event::NAME,
            $event,
            $environment->getDataDefinition()->getName(),
            $property->getName()
        );

        if ($event->getOptions() !== $options) {
            $options = $event->getOptions();
        }

        return $options;
    }

    /**
     * Decode a value from native data of the data provider to the widget via event.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @param ModelInterface       $model       The model.
     *
     * @param string               $property    The property.
     *
     * @param mixed                $value       The value of the property.
     *
     * @return mixed
     */
    public function decodeValue($environment, $model, $property, $value)
    {
        $event = new DecodePropertyValueForWidgetEvent($environment, $model);
        $event
            ->setProperty($property)
            ->setValue($value);

        $environment->getEventPropagator()->propagate(
            $event::NAME,
            $event,
            array(
                $environment->getDataDefinition()->getName(),
                $property
            )
        );

        return $event->getValue();
    }

    /**
     * Render a property value to readable text.
     *
     * @param RenderReadablePropertyValueEvent $event The event being processed.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function renderReadablePropertyValue(RenderReadablePropertyValueEvent $event)
    {
        if ($event->getRendered() !== null) {
            return;
        }

        $property = $event->getProperty();
        $value    = $this->decodeValue(
            $event->getEnvironment(),
            $event->getModel(),
            $event->getProperty()->getName(),
            $event->getValue()
        );

        $extra = $property->getExtra();

        // TODO: refactor - foreign key handling is not yet supported.
        /*
        if (isset($arrFieldConfig['foreignKey']))
        {
            $temp = array();
            $chunks = explode('.', $arrFieldConfig['foreignKey'], 2);


            foreach ((array) $value as $v)
            {
//                    $objKey = $this->Database->prepare("SELECT " . $chunks[1] . " AS value FROM " . $chunks[0] . " WHERE id=?")
//                            ->limit(1)
//                            ->execute($v);
//
//                    if ($objKey->numRows)
//                    {
//                        $temp[] = $objKey->value;
//                    }
            }

//                $row[$i] = implode(', ', $temp);
        }
        // Decode array
        else
         */
        if (is_array($value)) {
            foreach ($value as $kk => $vv) {
                if (is_array($vv)) {
                    $vals       = array_values($vv);
                    $value[$kk] = $vals[0] . ' (' . $vals[1] . ')';
                }
            }

            $event->setRendered(implode(', ', $value));
        } elseif (isset($extra['rgxp'])) {
            // Date format.
            if ($extra['rgxp'] == 'date') {
                $dateEvent = new ParseDateEvent($value, $GLOBALS['TL_CONFIG']['dateFormat']);
                $event->getDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

                $event->setRendered($dateEvent->getResult());
            } elseif ($extra['rgxp'] == 'time') {
                // Time format.
                $dateEvent = new ParseDateEvent($value, $GLOBALS['TL_CONFIG']['timeFormat']);
                $event->getDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

                $event->setRendered($dateEvent->getResult());
            }
        } elseif (isset($extra['rgxp']) && $extra['rgxp'] == 'datim' ||
            in_array(
                $property->getGroupingMode(),
                array(
                    ListingConfigInterface::GROUP_DAY,
                    ListingConfigInterface::GROUP_MONTH,
                    ListingConfigInterface::GROUP_YEAR
                )
            ) ||
            $property->getName() == 'tstamp'
        ) {
            // Date and time format.
            $dateEvent = new ParseDateEvent($value, $GLOBALS['TL_CONFIG']['timeFormat']);
            $event->getDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

            $event->setRendered($dateEvent->getResult());
        } elseif ($property->getWidgetType() == 'checkbox' && !$extra['multiple']) {
            $event->setRendered(strlen($value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no']);
        } elseif ($property->getWidgetType() == 'textarea' && ($extra['allowHtml'] || $extra['preserveTags'])) {
            $event->setRendered(nl2br_html5(specialchars($value)));
        } elseif (isset($extra['reference']) && is_array($extra['reference'])) {
            if (isset($extra['reference'][$value])) {
                $event->setRendered(
                    (is_array($extra['reference'][$value])
                        ? $extra['reference'][$value][0]
                        : $extra['reference'][$value])
                );
            }
        } elseif ($value instanceof \DateTime) {
            $dateEvent = new ParseDateEvent($value->getTimestamp(), $GLOBALS['TL_CONFIG']['datimFormat']);
            $event->getDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

            $event->setRendered($dateEvent->getResult());
        } else {
            $options = $property->getOptions();
            if (!$options) {
                $options = $this->getOptions($event->getEnvironment(), $event->getModel(), $event->getProperty());
            }
            if (array_is_assoc($options)) {
                $event->setRendered($options[$value]);
            }
        }
    }

    /**
     * Add custom twig extension.
     *
     * @param \ContaoTwigInitializeEvent $event The event.
     *
     * @return void
     */
    public function initTwig(\ContaoTwigInitializeEvent $event)
    {
        $contaoTwig  = $event->getContaoTwig();
        $environment = $contaoTwig->getEnvironment();

        $environment->addExtension(new DcGeneralExtension());
    }
}
