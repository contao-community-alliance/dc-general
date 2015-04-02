<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition;

/**
 * A generic data provider information.
 *
 * @package DcGeneral\DataDefinition
 */
class DataProviderInformation implements DataProviderInformationInterface
{
    /**
     * The name of the data provider information.
     *
     * @var string
     */
    protected $name;

    /**
     * Flag determining if versioning is enabled for this provider or not.
     *
     * @var bool
     */
    protected $versioningEnabled;

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
