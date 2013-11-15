<?php

namespace DcGeneral\Contao\Event;

use DcGeneral\Contao\BackendBindings;
use DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\View\Widget\Event\ResolveWidgetErrorMessageEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;

/**
 * Class Subscriber - gateway to the legacy Contao HOOK style callbacks.
 *
 * @package DcGeneral\Event
 */
class Subscriber
	implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return array
		(
			GetGlobalButtonEvent::NAME       => 'GetGlobalButton',
			GetGlobalButtonsEvent::NAME      => 'GetGlobalButtons',
			GetOperationButtonEvent::NAME    => 'GetOperationButton',

			ModelToLabelEvent::NAME          => 'ModelToLabel',
			GetGroupHeaderEvent::NAME        => 'GetGroupHeader',
			GetParentHeaderEvent::NAME       => 'GetParentHeader',
			ParentViewChildRecordEvent::NAME => 'ParentViewChildRecord',

			GetBreadcrumbEvent::NAME         => 'GetBreadcrumb',

			ResolveWidgetErrorMessageEvent::NAME => array('resolveWidgetErrorMessage', -1),
			GetPropertyOptionsEvent::NAME    => 'GetPropertyOptions',

			RenderReadablePropertyValueEvent::NAME => 'renderReadablePropertyValue',
		);
	}

	/**
	 * Triggers the globalButtonCallback() of the registered callback handler in the environment.
	 *
	 * @param GetGlobalButtonEvent $event
	 */
	public function GetGlobalButton(GetGlobalButtonEvent $event)
	{
		// Call a custom function instead of using the default button
		$strButtonCallback = $event->getEnvironment()->getCallbackHandler()->globalButtonCallback(
			$event->getKey(),
			$event->getLabel(),
			$event->getTitle(),
			$event->getAttributes(),
			$event->getEnvironment()->getDataDefinition()->getName(),
			$event->getEnvironment()->getRootIds()
		);

		if (!is_null($strButtonCallback))
		{
			$event
				->setHtml($strButtonCallback)
				->stopPropagation();

			return;
		}
	}


	public function GetGlobalButtons(GetGlobalButtonsEvent $event)
	{
		// TODO: there was no callback in Contao for this event. We might want to add it?
	}

	/**
	 * Triggers the globalButtonCallback() of the registered callback handler in the environment.
	 *
	 * @param GetOperationButtonEvent $event
	 */
	public function GetOperationButton(GetOperationButtonEvent $event)
	{
		$arrOperation = array_merge(array
			(
				'href' => $event->getHref(),
			),
			$event->getCommand()->getExtra()->getArrayCopy()
		);

		// Call a custom function instead of using the default button
		$strButtonCallback = $event->getEnvironment()->getCallbackHandler()->buttonCallback(
			$event->getModel(),
			$arrOperation,
			$event->getLabel(),
			$event->getTitle(),
			$event->getAttributes(),
			$event->getEnvironment()->getDataDefinition()->getName(),
			$event->getEnvironment()->getRootIds(),
			$event->getChildRecordIds(),
			$event->getCircularReference(),
			$event->getPrevious(),
			$event->getNext()
		);

		if (!is_null($strButtonCallback))
		{
			$event
				->setHtml($strButtonCallback)
				->stopPropagation();

			return;
		}
	}

	/**
	 * Triggers the labelCallback() of the registered callback handler in the environment.
	 *
	 * @param ModelToLabelEvent $event
	 */
	public function ModelToLabel(ModelToLabelEvent $event)
	{
		// Call label callback
		$mixedArgs = $event->getEnvironment()
			->getCallbackHandler()->labelCallback(
				$event->getModel(),
				$event->getLabel(),
				$event->getListLabel(),
				$event->getArgs()
			);

		if ($mixedArgs)
		{
			$event->setArgs($mixedArgs);
		}
	}

	public function GetGroupHeader(GetGroupHeaderEvent $event)
	{
		$event->setGroupField(
			$event->getEnvironment()
			->getCallbackHandler()->groupCallback(
				$event->getGroupField(),
				$event->getSortingMode(),
				$event->getValue(),
				$event->getModel()
			)
		);
	}

	public function GetParentHeader(GetParentHeaderEvent $event)
	{
		$additional = $event->getEnvironment()
			->getCallbackHandler()->headerCallback($event->getAdditional());

		if ($additional !== null)
		{
			$event->setAdditional($additional);
		}
	}

	/**
	 * Handles $GLOBALS['TL_DCA']['tl_*']['list']['sorting']['child_record_class'] callbacks.
	 *
	 * @param ParentViewChildRecordEvent $event
	 */
	public function ParentViewChildRecord(ParentViewChildRecordEvent $event)
	{
		$html = $event->getEnvironment()
			->getCallbackHandler()->childRecordCallback($event->getModel());

		if ($html !== null)
		{
			$event->setHtml($html);
		}
	}

	public function GetBreadcrumb(GetBreadcrumbEvent $event)
	{
		$arrReturn = $event->getEnvironment()
			->getCallbackHandler()->generateBreadcrumb();

		$event->setElements($arrReturn);
	}

	public function resolveWidgetErrorMessage(ResolveWidgetErrorMessageEvent $event)
	{
		$error = $event->getError();

		if ($error instanceof \Exception)
		{
			$event->setError($error->getMessage());
		}
		else if (is_object($error))
		{
			if (method_exists($error, '__toString'))
			{
				$event->setError((string) $error);
			}
			else
			{
				$event->setError(sprintf('[%s]', get_class($error)));
			}
		}
		else if (!is_string($error))
		{
			$event->setError(sprintf('[%s]', gettype($error)));
		}
	}

	public function GetPropertyOptions(GetPropertyOptionsEvent $event)
	{
		$arrReturn = $event->getEnvironment()
			->getCallbackHandler()->optionsCallback($event->getFieldName());

		$event->setOptions($arrReturn);
	}

	public function renderReadablePropertyValue(RenderReadablePropertyValueEvent $event)
	{
		if ($event->getRendered() !== null) {
			return;
		}

		$property = $event->getProperty();
		$value    = $event->getValue();

		$extra = $property->getExtra();

		/*
		 * TODO refactor
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
					$vals = array_values($vv);
					$value[$kk] = $vals[0] . ' (' . $vals[1] . ')';
				}
			}

			$event->setRendered(implode(', ', $value));
		}
		// Date Formate
		else if ($extra['rgxp'] == 'date')
		{
			$event->setRendered(BackendBindings::parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $value));
		}
		// Date Formate
		else if ($extra['rgxp'] == 'time')
		{
			$event->setRendered(BackendBindings::parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $value));
		}
		// Date Formate
		else if (
			$extra['rgxp'] == 'datim' ||
			in_array($property->getGroupingMode(), array(ListingConfigInterface::GROUP_DAY, ListingConfigInterface::GROUP_MONTH, ListingConfigInterface::GROUP_YEAR)) ||
			$property->getName() == 'tstamp'
		) {
			$event->setRendered(BackendBindings::parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $value));
		}
		else if ($property->getWidgetType() == 'checkbox' && !$extra['multiple'])
		{
			$event->setRendered(strlen($value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no']);
		}
		else if ($property->getWidgetType() == 'textarea' && ($extra['allowHtml'] || $extra['preserveTags']))
		{
			$event->setRendered(nl2br_html5(specialchars($value)));
		}
		/**
		 * TODO refactor
		else if (is_array($arrFieldConfig['reference']))
		{
			return isset($arrFieldConfig['reference'][$mixModelField]) ?
				((is_array($arrFieldConfig['reference'][$mixModelField])) ?
					$arrFieldConfig['reference'][$mixModelField][0] :
					$arrFieldConfig['reference'][$mixModelField]) :
				$mixModelField;
		}
		 */
		else if (array_is_assoc($property->getOptions()))
		{
			$options = $property->getOptions();
			$event->setRendered($options[$value]);
		}
		else if ($value instanceof \DateTime) {
			$event->setRendered(BackendBindings::parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $value->getTimestamp()));
		}
	}
}
