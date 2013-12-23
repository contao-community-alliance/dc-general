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

namespace DcGeneral\Event;

/**
 * This event is emitted after a model has been saved to the data provider.
 *
 * @package DcGeneral\Event
 */
class PostPersistModelEvent extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.model.post-persist';
}
