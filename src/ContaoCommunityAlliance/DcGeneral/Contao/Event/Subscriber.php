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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Event;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ResolveWidgetErrorMessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Subscriber - gateway to the legacy Contao HOOK style callbacks.
 *
 * @package DcGeneral\Event
 */
class Subscriber
	implements EventSubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array
		(
			ResolveWidgetErrorMessageEvent::NAME => array('resolveWidgetErrorMessage', -1),

			RenderReadablePropertyValueEvent::NAME => 'renderReadablePropertyValue',
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

		if ($error instanceof \Exception)
		{
			$event->setError($error->getMessage());
		}
		elseif (is_object($error))
		{
			if (method_exists($error, '__toString'))
			{
				$event->setError((string)$error);
			}
			else
			{
				$event->setError(sprintf('[%s]', get_class($error)));
			}
		}
		elseif (!is_string($error))
		{
			$event->setError(sprintf('[%s]', gettype($error)));
		}
	}

	/**
	 * Render a property value to readable text.
	 *
	 * @param RenderReadablePropertyValueEvent $event The event being processed.
	 *
	 * @return void
	 */
	public function renderReadablePropertyValue(RenderReadablePropertyValueEvent $event)
	{
		if ($event->getRendered() !== null)
		{
			return;
		}

		$property = $event->getProperty();
		$value    = $event->getValue();

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
		if (is_array($value))
		{
			foreach ($value as $kk => $vv)
			{
				if (is_array($vv))
				{
					$vals       = array_values($vv);
					$value[$kk] = $vals[0] . ' (' . $vals[1] . ')';
				}
			}

			$event->setRendered(implode(', ', $value));
		}
		// Date format.
		elseif ($extra['rgxp'] == 'date')
		{
			$dateEvent = new ParseDateEvent($value, $GLOBALS['TL_CONFIG']['dateFormat']);
			$event->getDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

			$event->setRendered($dateEvent->getResult());
		}
		// Time format.
		elseif ($extra['rgxp'] == 'time')
		{
			$dateEvent = new ParseDateEvent($value, $GLOBALS['TL_CONFIG']['timeFormat']);
			$event->getDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

			$event->setRendered($dateEvent->getResult());
		}
		// Date and time format.
		elseif ($extra['rgxp'] == 'datim' ||
			in_array(
				$property->getGroupingMode(),
				array(
					ListingConfigInterface::GROUP_DAY,
					ListingConfigInterface::GROUP_MONTH,
					ListingConfigInterface::GROUP_YEAR)
			) ||
			$property->getName() == 'tstamp'
		)
		{
			$dateEvent = new ParseDateEvent($value, $GLOBALS['TL_CONFIG']['timeFormat']);
			$event->getDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

			$event->setRendered($dateEvent->getResult());
		}
		elseif ($property->getWidgetType() == 'checkbox' && !$extra['multiple'])
		{
			$event->setRendered(strlen($value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no']);
		}
		elseif ($property->getWidgetType() == 'textarea' && ($extra['allowHtml'] || $extra['preserveTags']))
		{
			$event->setRendered(nl2br_html5(specialchars($value)));
		}
		// TODO: refactor - reference handling is not yet supported.
		/**
		else if (is_array($arrFieldConfig['reference']))
		{
			return isset($arrFieldConfig['reference'][$mixModelField]) ?
				((is_array($arrFieldConfig['reference'][$mixModelField])) ?
					$arrFieldConfig['reference'][$mixModelField][0] :
					$arrFieldConfig['reference'][$mixModelField]) :
				$mixModelField;
		}
		 */
		elseif (array_is_assoc($property->getOptions()))
		{
			$options = $property->getOptions();
			$event->setRendered($options[$value]);
		}
		elseif ($value instanceof \DateTime)
		{
			$dateEvent = new ParseDateEvent($value->getTimestamp(), $GLOBALS['TL_CONFIG']['datimFormat']);
			$event->getDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

			$event->setRendered($dateEvent->getResult());
		}
	}
}
