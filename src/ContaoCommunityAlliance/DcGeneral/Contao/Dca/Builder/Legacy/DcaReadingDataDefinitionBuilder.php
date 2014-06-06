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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\LoadDataContainerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Builder\AbstractEventDrivenDataDefinitionBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
	public function loadDca($dcaName, EventDispatcherInterface $dispatcher)
	{
		$this->dca = null;
		$event     = new LoadDataContainerEvent($dcaName, false);
		$dispatcher->dispatch(ContaoEvents::CONTROLLER_LOAD_DATA_CONTAINER, $event);

		if (isset($GLOBALS['TL_DCA'][$dcaName]))
		{
			$this->dca = $GLOBALS['TL_DCA'][$dcaName];
		}

		$event = new LoadLanguageFileEvent($dcaName);
		$dispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, $event);

		return $this->dca !== null;
	}

	/**
	 * Read the specified sub path from the dca.
	 *
	 * @param string $path The path from the Dca to read.
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
