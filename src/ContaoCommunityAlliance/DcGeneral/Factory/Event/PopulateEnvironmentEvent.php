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

namespace ContaoCommunityAlliance\DcGeneral\Factory\Event;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

class PopulateEnvironmentEvent extends AbstractEnvironmentAwareEvent
{
	const NAME = 'dc-general.factory.populate-environment';
}
