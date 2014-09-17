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

namespace ContaoCommunityAlliance\DcGeneral\Event;

/**
 * This event is emitted after a model has been pasted.
 *
 * @package DcGeneral\Event
 */
class PostPasteModelEvent extends AbstractModelAwareEvent
{
    const NAME = 'dc-general.model.post-paste';
}
