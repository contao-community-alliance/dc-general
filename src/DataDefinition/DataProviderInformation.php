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
class DataProviderInformation implements DataProviderInformationInterface
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
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setVersioningEnabled($versioningEnabled)
    {
        $this->versioningEnabled = $versioningEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isVersioningEnabled()
    {
        return $this->versioningEnabled;
    }
}
