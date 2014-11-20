<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Interface FilterInterface.
 *
 * This interface contains filters to fetch items from the clipboard.
 */
interface FilterInterface
{
    /**
     * Determine if the item is accepted.
     *
     * @param ItemInterface $item The clipboard item.
     *
     * @return bool
     */
    public function accepts(ItemInterface $item);
}
