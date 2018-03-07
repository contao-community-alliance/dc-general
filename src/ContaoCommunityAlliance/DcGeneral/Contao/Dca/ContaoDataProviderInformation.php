<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\DataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;

/**
 * Class ContaoDataProviderInformation.
 *
 * This Information holds the details of a Contao data provider definition.
 */
class ContaoDataProviderInformation extends DataProviderInformation
{
    /**
     * The table name to use.
     *
     * @var string
     */
    protected $tableName;

    /**
     * Name of the provider class to use.
     *
     * @var string
     */
    protected $className = DefaultDataProvider::class;

    /**
     * Custom initialization data to be passed to the constructor of the data provider class.
     *
     * @var mixed
     */
    protected $initializationData;

    /**
     * Set the table name of the data provider.
     *
     * @param string $tableName The name of the table in the database.
     *
     * @return ContaoDataProviderInformation
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Retrieve the table name of the data provider.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set the data provider class to use, defaults to 'ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider'.
     *
     * @param string $className The name of the data provider class to use.
     *
     * @return ContaoDataProviderInformation
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Retrieve the data provider class to use.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set the data to use for initialization of the data provider.
     *
     * The nature of this data is subject to the concrete implementation of the data provider defined as the class to
     * use.
     *
     * @param mixed $initializationData The initialization data the data provider class expects.
     *
     * @return ContaoDataProviderInformation
     */
    public function setInitializationData($initializationData)
    {
        $this->initializationData = $initializationData;

        return $this;
    }

    /**
     * Retrieve the data to use for initialization of the data provider.
     *
     * @return mixed
     */
    public function getInitializationData()
    {
        return $this->initializationData;
    }
}
