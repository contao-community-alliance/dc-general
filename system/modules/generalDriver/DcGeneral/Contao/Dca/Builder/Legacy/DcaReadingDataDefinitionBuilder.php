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

/**
 * Build the container config from legacy DCA syntax.
 */
abstract class DcaReadingDataDefinitionBuilder extends AbstractEventDrivenDataDefinitionBuilder
{
	/**
	 * Buffer for the DCA.
	 *
	 * @var array
	 */
	protected $dca;

	/**
	 * {@inheritdoc}
	 */
	public function loadDca($dcaName)
	{
		$this->dca         = null;
		$previousDca       = $GLOBALS['TL_DCA'];
		$GLOBALS['TL_DCA'] = array();

		BackendBindings::loadDataContainer($dcaName, true);

		if (isset($GLOBALS['TL_DCA'][$dcaName]))
		{
			$this->dca = $GLOBALS['TL_DCA'][$dcaName];
		}
		$GLOBALS['TL_DCA']  = $previousDca;
		unset($GLOBALS['loadDataContainer'][$dcaName]);

		return $this->dca !== null;
	}

	/**
	 * Read the specified sub path from the dca.
	 *
	 * @param string $path
	 *
	 * @return mixed
	 *
	 * @internal
	 */
	protected function getFromDca($path)
	{
		$chunks = explode('/', trim($path, '/'));
		$dca    = $this->dca;

		while (($chunk = array_shift($chunks)) !== null)
		{
			if (!array_key_exists($chunk, $dca))
			{
				return null;
			}

			$dca = $dca[$chunk];
		}

		return $dca;
	}
}
