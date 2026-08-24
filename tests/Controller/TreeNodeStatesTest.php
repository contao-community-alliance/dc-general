<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Controller;

use ContaoCommunityAlliance\DcGeneral\Controller\TreeNodeStates;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * The "expand all / collapse all" toggle (contao-community-alliance/dc-general#560) relies on
 * isModelOpen() honouring the "all" flag the same way isAllOpen() does - it used to compare the
 * flag with a strict `1 === $value`, so the boolean true written by setAllOpen() never matched.
 */
#[CoversClass(TreeNodeStates::class)]
final class TreeNodeStatesTest extends TestCase
{
    public function testIsAllOpenDefaultsToFalseWithoutAnyState(): void
    {
        $states = new TreeNodeStates();

        self::assertFalse($states->isAllOpen());
    }

    public function testIsModelOpenHonoursTheBooleanAllFlag(): void
    {
        $states = new TreeNodeStates();
        $states->setAllOpen(true);

        self::assertTrue($states->isModelOpen('provider', 'some-id'));
    }

    public function testIsModelOpenIgnoresTheAllFlagWhenRequested(): void
    {
        $states = new TreeNodeStates();
        $states->setAllOpen(true);

        self::assertFalse($states->isModelOpen('provider', 'some-id', true));
    }

    public function testToggleAllRoundTripFlipsBetweenOpenAndClosed(): void
    {
        $states = new TreeNodeStates();
        self::assertFalse($states->isAllOpen());

        // Mirrors TreeView::handleNodeStateChanges() for `ptg=all`.
        $wasAllOpen = $states->isAllOpen();
        $states->resetAll();
        $states->setAllOpen(!$wasAllOpen);
        self::assertTrue($states->isAllOpen());

        $wasAllOpen = $states->isAllOpen();
        $states->resetAll();
        $states->setAllOpen(!$wasAllOpen);
        self::assertFalse($states->isAllOpen());
    }

    public function testResetAllClearsPerNodeOverridesButKeepsTheAllFlag(): void
    {
        $states = new TreeNodeStates();
        $states->setAllOpen(true);
        $states->setModelState('provider', 'some-id', false);
        self::assertFalse($states->isModelOpen('provider', 'some-id', true));

        $states->resetAll();

        self::assertTrue($states->isAllOpen());
        self::assertFalse($states->isModelOpen('provider', 'some-id', true));
    }
}
