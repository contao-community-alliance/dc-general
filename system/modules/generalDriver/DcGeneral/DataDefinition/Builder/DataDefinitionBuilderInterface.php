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

namespace DcGeneral\DataDefinition\Parser;

use DcGeneral\EnvironmentInterface;

interface DataDefinitionBuilderInterface
{
	/**
	 * Build a data definition and store it into the environments container.
	 *
	 * @param EnvironmentInterface $environment
	 */
	public function build(EnvironmentInterface $environment);
}
