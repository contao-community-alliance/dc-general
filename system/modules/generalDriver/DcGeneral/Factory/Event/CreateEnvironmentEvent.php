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

namespace DcGeneral\Factory\Event;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Events\BaseEvent;

class CreateEnvironmentEvent extends BaseEvent
{
	const NAME = 'DcGeneral\Factory\Event\CreateEnvironment';

	function __construct(EnvironmentInterface $environment)
	{
		$this->setEnvironment($environment);
	}
}
