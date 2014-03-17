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

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;

/**
 * Class ContainerHeaderCallbackListener.
 *
 * Handler for the header row in parent list mode.
 *
 * @package DcGeneral\Contao\Callback
 */
class ContainerHeaderCallbackListener extends AbstractReturningCallbackListener
{
	/**
	 * Retrieve the arguments for the callback.
	 *
	 * @param GetParentHeaderEvent $event The event being emitted.
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		return array($event->getAdditional(), new DcCompat($event->getEnvironment()));
	}

	/**
	 * Update the event with the information returned by the callback.
	 *
	 * @param GetParentHeaderEvent $event The event being emitted.
	 *
	 * @param array                $value The additional information.
	 *
	 * @return void
	 */
	public function update($event, $value)
	{
		if (is_null($value))
		{
			return;
		}

		$event->setAdditional($value);
		$event->stopPropagation();
	}
}
