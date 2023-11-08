<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\LoadDataContainerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Builder\AbstractEventDrivenDataDefinitionBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function array_key_exists;
use function array_shift;
use function explode;
use function is_array;
use function trim;

/**
 * Build the container config from legacy DCA syntax.
 */
abstract class DcaReadingDataDefinitionBuilder extends AbstractEventDrivenDataDefinitionBuilder
{
    /**
     * Buffer for the DCA.
     *
     * @var array|null
     */
    protected $dca = null;

    /**
     * Load the dca data.
     *
     * @param string                   $dcaName    The name of the data container.
     * @param EventDispatcherInterface $dispatcher The event dispatcher to use.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function loadDca($dcaName, EventDispatcherInterface $dispatcher)
    {
        $this->dca = null;

        $dispatcher
            ->dispatch(new LoadDataContainerEvent($dcaName, false), ContaoEvents::CONTROLLER_LOAD_DATA_CONTAINER);

        if (isset($GLOBALS['TL_DCA'][$dcaName])) {
            $this->dca = $GLOBALS['TL_DCA'][$dcaName];
        }

        $dispatcher->dispatch(new LoadLanguageFileEvent($dcaName), ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE);

        return null !== $this->dca;
    }

    /**
     * Read the specified sub path from the dca.
     *
     * @param string $path The path from the Dca to read.
     *
     * @return mixed
     */
    protected function getFromDca($path)
    {
        $chunks = explode('/', trim($path, '/'));
        $dca    = $this->dca;

        while (null !== ($chunk = array_shift($chunks))) {
            if (!(is_array($dca) && array_key_exists($chunk, $dca))) {
                return null;
            }

            $dca = $dca[$chunk];
        }

        return $dca;
    }
}
