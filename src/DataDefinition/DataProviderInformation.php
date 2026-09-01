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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition;

/**
 * A generic data provider information.
 */
class DataProviderInformation implements DataProviderInformationInterface, LoggingInformationInterface
{
    /**
     * The name of the data provider information.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Flag determining if versioning is enabled for this provider or not.
     *
     * @var bool
     */
    protected $versioningEnabled = false;

    /**
     * Flag determining if create/duplicate/delete are logged for this provider or not.
     *
     * Defaults to enabled - this mirrors what Contao logs for its own tables, an unconfigured
     * provider should not silently lose that.
     *
     * @var bool
     */
    protected bool $loggingEnabled = true;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setVersioningEnabled($versioningEnabled)
    {
        $this->versioningEnabled = $versioningEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isVersioningEnabled()
    {
        return $this->versioningEnabled;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setLoggingEnabled(bool $loggingEnabled): self
    {
        $this->loggingEnabled = $loggingEnabled;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function isLoggingEnabled(): bool
    {
        return $this->loggingEnabled;
    }
}
