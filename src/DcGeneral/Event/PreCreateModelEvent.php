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
 * This event is emitted before a model is created.
 *
 * TODO: how is this event possible? How to pass a model before it is created?
 *
 * @package DcGeneral\Event
 */
class PreCreateModelEvent extends AbstractModelAwareEvent
{
	const NAME = 'dc-general.model.pre-create';
}
