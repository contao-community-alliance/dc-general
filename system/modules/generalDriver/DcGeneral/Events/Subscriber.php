<?php

namespace DcGeneral\Events;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DcGeneral\View\DefaultView\Events\GetGroupHeaderEvent;
use DcGeneral\View\DefaultView\Events\GetParentHeaderEvent;
use DcGeneral\View\DefaultView\Events\GetPasteRootButtonEvent;
use DcGeneral\View\DefaultView\Events\ModelToLabelEvent;
use DcGeneral\View\DefaultView\Events\GetOperationButtonEvent;
use DcGeneral\View\DefaultView\Events\GetPasteButtonEvent;
use DcGeneral\View\DefaultView\Events\GetGlobalButtonsEvent;
use DcGeneral\View\DefaultView\Events\GetGlobalButtonEvent;

/**
 * Class Subscriber - gateway to the legacy Contao HOOK style callbacks.
 *
 * @package DcGeneral\Events
 */
class Subscriber
	implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return array
		(
			GetGlobalButtonsEvent::NAME   => 'GetGlobalButtons',
			GetGlobalButtonEvent::NAME    => 'GetGlobalButton',
			GetOperationButtonEvent::NAME => 'GetOperationButton',
			GetPasteButtonEvent::NAME     => 'GetPasteButton',
			GetPasteRootButtonEvent::NAME => 'GetPasteRootButton',

			ModelToLabelEvent::NAME       => 'ModelToLabel',
			GetGroupHeaderEvent::NAME     => 'GetGroupHeader',
			GetParentHeaderEvent::NAME    => 'GetParentHeader',
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
}
