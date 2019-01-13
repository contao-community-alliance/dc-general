<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\FilterInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;

/**
 * Mocked Filter class returns just a predefined value.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Test\Clipboard
 */
class MockedFilter implements FilterInterface
{
    /**
     * Accepts state.
     *
     * @var bool
     */
    private $accepts;

    /**
     * MockedFilter constructor.
     *
     * @param bool $accepts Accept state.
     */
    public function __construct($accepts)
    {
        $this->accepts = (bool) $accepts;
    }

    /**
     * {@inheritDoc}
     */
    public function accepts(ItemInterface $item)
    {
        return $this->accepts;
    }
}
