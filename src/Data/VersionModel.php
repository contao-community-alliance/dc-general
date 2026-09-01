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

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * A single row of the "tl_version" listing (one entry of the version drop-down in the edit mask).
 *
 * Reference implementation of {@see VersionModelInterface}, reading the version, tstamp, username
 * and active properties {@see DataProviderInterface::getVersions()} already populates on a plain
 * model via setProperty(). "tl_version" does not store a separate real name for the author, only
 * the username - the same as Contao's own native versioning (Contao\Versions::renderDropdown())
 * displays - so getAuthorName() and getAuthorEmail() are deliberately empty, letting the template
 * fall back to the username.
 *
 * @api
 */
class VersionModel extends DefaultModel implements VersionModelInterface
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getVersion()
    {
        return (string) $this->getProperty('version');
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function isCurrent()
    {
        return (bool) $this->getProperty('active');
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getDateTime()
    {
        return (new \DateTime())->setTimestamp((int) $this->getProperty('tstamp'));
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getAuthorName()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getAuthorUsername()
    {
        return (string) $this->getProperty('username');
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getAuthorEmail()
    {
        return '';
    }
}
