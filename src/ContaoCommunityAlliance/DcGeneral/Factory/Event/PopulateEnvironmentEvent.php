<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Factory\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * This event is emitted when an environment gets populated.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Factory\Event
 */
class PopulateEnvironmentEvent extends AbstractEnvironmentAwareEvent
{
    const NAME = 'dc-general.factory.populate-environment';
}
