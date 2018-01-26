<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function loadDca($dcaName, EventDispatcherInterface $dispatcher)
    {
        $this->dca = null;
        $event     = new LoadDataContainerEvent($dcaName, false);
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_LOAD_DATA_CONTAINER, $event);

        if (isset($GLOBALS['TL_DCA'][$dcaName])) {
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
     */
    protected function getFromDca($path)
    {
        $chunks = explode('/', trim($path, '/'));
        $dca    = $this->dca;

        while (($chunk = array_shift($chunks)) !== null) {
            if (!(is_array($dca) && array_key_exists($chunk, $dca))) {
                return null;
            }

            $dca = $dca[$chunk];
        }

        return $dca;
    }
}
