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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition;

/**
 * Whether create/duplicate/delete on this data provider are written to the Contao system log
 * (tl_log). A separate interface rather than an addition to DataProviderInformationInterface,
 * which would break every existing implementation - see ".claude/dcg-systemlog.md". A data
 * provider information that does not implement this interface counts as "not configured", which
 * is treated the same as enabled - logging mirrors what Contao already does for its own tables, so
 * an unconfigured provider should not silently lose that.
 */
interface LoggingInformationInterface
{
    /**
     * Determine if the create/duplicate/delete of a record shall be logged.
     *
     * @return bool
     */
    public function isLoggingEnabled(): bool;

    /**
     * Set if the create/duplicate/delete of a record shall be logged.
     *
     * @param bool $loggingEnabled The flag.
     *
     * @return $this
     */
    public function setLoggingEnabled(bool $loggingEnabled): self;
}
