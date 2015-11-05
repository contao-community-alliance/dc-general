<?php

/**
 * @package    westwerk
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\ClipBoard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\FilterInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;

/**
 * Mocked Filter class returns just a predefined value.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Test\ClipBoard
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
