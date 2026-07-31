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

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Panel;

/**
 * A limit element that can tell how many records there are in total.
 *
 * The limit element has to determine that number anyway in order to build its own options, so it is
 * the one place where it is available without querying a second time. Anything that wants to render
 * a pagination needs it.
 *
 * This is a separate interface on purpose: adding the method to {@see LimitElementInterface} would
 * break every implementation out there. It is to be merged into that interface in 3.0.
 *
 * @api
 */
interface TotalAwareLimitElementInterface
{
    /**
     * Retrieve the total amount of records matching the current filter, ignoring offset and amount.
     *
     * @return int
     */
    public function getTotal(): int;
}
