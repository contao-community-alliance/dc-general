<?php

namespace DcGeneral\Event;

use DcGeneral\View\BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\View\Widget\Event\ResolveWidgetErrorMessageEvent;
use DcGeneral\View\BackendView\Event\GetPropertyOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DcGeneral\View\BackendView\Event\GetGlobalButtonEvent;
use DcGeneral\View\BackendView\Event\GetGlobalButtonsEvent;
use DcGeneral\View\BackendView\Event\GetGroupHeaderEvent;
use DcGeneral\View\BackendView\Event\GetOperationButtonEvent;
use DcGeneral\View\BackendView\Event\GetParentHeaderEvent;
use DcGeneral\View\BackendView\Event\GetPasteRootButtonEvent;
use DcGeneral\View\BackendView\Event\GetPasteButtonEvent;
use DcGeneral\View\BackendView\Event\ModelToLabelEvent;
use DcGeneral\View\BackendView\Event\ParentViewChildRecordEvent;

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
			GetPasteButtonEvent::NAME        => 'GetPasteButton',
			GetPasteRootButtonEvent::NAME    => 'GetPasteRootButton',

			ModelToLabelEvent::NAME          => 'ModelToLabel',
			GetGroupHeaderEvent::NAME        => 'GetGroupHeader',
			GetParentHeaderEvent::NAME       => 'GetParentHeader',
			ParentViewChildRecordEvent::NAME => 'ParentViewChildRecord',

			GetBreadcrumbEvent::NAME         => 'GetBreadcrumb',

			ResolveWidgetErrorMessageEvent::NAME => array('resolveWidgetErrorMessage', -1),
			GetPropertyOptionsEvent::NAME    => 'GetPropertyOptions',
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
		// Call a custom function instead of using the default button
		$strButtonCallback = $event->getEnvironment()->getCallbackHandler()->buttonCallback(
			$event->getModel(),
			$event->getObjOperation(),
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
	 * Triggers the globalButtonCallback() of the registered callback handler in the environment.
	 *
	 * @param GetPasteButtonEvent $event
	 */
	public function GetPasteButton(GetPasteButtonEvent $event)
	{
		// Callback for paste btt
		$strButtonCallback = $event->getEnvironment()
			->getCallbackHandler()
			->pasteButtonCallback(
				($objModel = $event->getModel()) ? $objModel->getPropertiesAsArray() : null,
				$event->getEnvironment()->getDataDefinition()->getName(),
				$event->getCircularReference(),
				$event->getEnvironment()->getClipboard()->getContainedIds(),
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
	 * Triggers the globalButtonCallback() of the registered callback handler in the environment.
	 *
	 * @param GetPasteRootButtonEvent $event
	 */
	public function GetPasteRootButton(GetPasteRootButtonEvent $event)
	{
		// Callback for paste btt
		$strButtonCallback = $event->getEnvironment()
			->getCallbackHandler()
			->pasteButtonCallback(
				$event->getEnvironment()->getDataDriver()->getEmptyModel()->getPropertiesAsArray(),
				$event->getEnvironment()->getDataDefinition()->getName(),
				false,
				$event->getEnvironment()->getClipboard()->getContainedIds(),
				null,
				null
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
}
