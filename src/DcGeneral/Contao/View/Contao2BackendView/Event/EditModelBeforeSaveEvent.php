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

namespace DcGeneral\Contao\View\Contao2BackendView\Event;

use DcGeneral\Event\AbstractModelAwareEvent;

/**
 * Class EditModelBeforeSaveEvent.
 *
 * This event gets issued just before a model will get passed to the data provider for saving.
 * You can subscribe to it to manipulate the model just before saving to the data provider.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class EditModelBeforeSaveEvent
	extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.view.contao2backend.edit.before-save-model';
}

