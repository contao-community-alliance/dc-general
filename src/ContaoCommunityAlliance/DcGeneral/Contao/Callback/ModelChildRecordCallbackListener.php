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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;

/**
 * Class ModelChildRecordCallbackListener.
 *
 * Handler for the child record callbacks.
 *
 * @package DcGeneral\Contao\Callback
 */
class ModelChildRecordCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * Retrieve the arguments for the callback.
	 *
	 * @param ParentViewChildRecordEvent $event The event being emitted.
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array(
			$event->getModel()->getPropertiesAsArray()
		);
	}

	/**
	 * Set the HTML code for the button.
	 *
	 * @param ParentViewChildRecordEvent $event The event being emitted.
	 *
	 * @param string                     $value The value returned by the callback.
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value))
		{
			return;
		}

		$event->setHtml($value);
		$event->stopPropagation();
	}
}
