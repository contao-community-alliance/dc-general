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

namespace DcGeneral\Contao\Callback;

use DcGeneral\Contao\Compatibility\DcCompat;
use DcGeneral\Event\PostDeleteModelEvent;

/**
 * Class ContainerOnDeleteCallbackListener.
 *
 * Handler for delete callbacks.
 *
 * @package DcGeneral\Contao\Callback
 */
class ContainerOnDeleteCallbackListener extends AbstractCallbackListener
{
	/**
	 * Retrieve the arguments for the callback.
	 *
	 * @param PostDeleteModelEvent $event The event being emitted.
	 *
	 * @return array
	 */
	public function getArgs($event)
	{
		// TODO: Find a way to get tl_undo record ID here.
		return array(new DcCompat($event->getEnvironment(), $event->getModel()), 0);
	}
}
