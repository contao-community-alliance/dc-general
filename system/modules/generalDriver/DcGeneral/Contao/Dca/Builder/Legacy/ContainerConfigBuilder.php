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

namespace DcGeneral\Contao\Dca\Builder\Legacy;

use DcGeneral\DataDefinition\Builder\AbstractEventDrivenDataDefinitionBuilder;
use DcGeneral\EnvironmentInterface;

/**
 * Build the container config from legacy DCA syntax.
 */
class ContainerConfigBuilder extends AbstractEventDrivenDataDefinitionBuilder
{
	const PRIORITY = 100;

	/**
	 * {@inheritdoc}
	 */
	public function build(EnvironmentInterface $environment)
	{
		// TODO: Implement build() method.
	}
}
