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

use DcGeneral\Contao\BackendBindings;
use DcGeneral\DataDefinition\Builder\AbstractEventDrivenDataDefinitionBuilder;
use DcGeneral\DataDefinition\ContainerInterface;

/**
 * Build the container config from legacy DCA syntax.
 */
class LegacyDcaDataDefinitionBuilder extends AbstractEventDrivenDataDefinitionBuilder
{
	const PRIORITY = 100;

	/**
	 * {@inheritdoc}
	 */
	public function build(ContainerInterface $container)
	{
		$name              = $container->getName();
		$previousDca       = $GLOBALS['TL_DCA'];
		$GLOBALS['TL_DCA'] = array();

		BackendBindings::loadDataContainer($name, true);

		$localDca           = $GLOBALS['TL_DCA'];
		$GLOBALS['TL_DCA']  = $previousDca;

		if (!isset($GLOBALS['TL_DCA'][$name]))
		{
			return;
		}

		// TODO parse $localDca variable into $container
	}
}
