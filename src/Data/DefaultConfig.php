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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Class DefaultConfig.
 *
 * This class is the default implementation of the ConfigInterface.
 */
class DefaultConfig implements ConfigInterface
{
    /**
     * The id of the element to be retrieved.
     *
     * @var mixed
     */
    protected $mixId;

    /**
     * The ids to be retrieved.
     *
     * @var array
     *
     * @deprecated This is deprecated since 2.1 and will be removed in 3.0.
     */
    protected $arrIds = [];

    /**
     * Flag determining if only the ids shall get fetched or models.
     *
     * @var bool
     *
     * @see fetch
     */
    protected $blnIdOnly = false;

    /**
     * Offset for retrieving entries.
     *
     * @var int
     */
    protected $intStart = 0;

    /**
     * Amount of entries to be retrieved.
     *
     * @var int
     */
    protected $intAmount = 0;

    /**
     * The filters to use.
     *
     * @var array|null
     */
    protected $arrFilter;

    /**
     * The properties to use for sorting.
     *
     * @var array(string => string)
     */
    protected $arrSorting = [];

    /**
     * The properties to retrieve.
     *
     * @var array|null
     */
    protected $arrFields;

    /**
     * Miscellaneous arbitrary data stored in the config.
     *
     * @var array
     *
     * @see set
     * @see get
     */
    protected $arrData = [];

    /**
     * Create object.
     *
     * Private as only the data provider shall know how to instantiate.
     */
    private function __construct()
    {
        return $this;
    }

    /**
     * Static constructor.
     *
     * @return ConfigInterface
     */
    public static function init()
    {
        return new static();
    }

    /**
     * Get specific id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->mixId;
    }

    /**
     * Set a specific id for an element to be retrieved.
     *
     * @param mixed $currentId The id of the element to be retrieved.
     *
     * @return ConfigInterface
     */
    public function setId($currentId)
    {
        $this->mixId = $currentId;

        return $this;
    }

    /**
     * Get list of specific ids to be retrieved.
     *
     * @return array
     */
    public function getIds()
    {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'The method setids in the DefaultConfig is deprecated since 2.1 and will be removed in 3.0.',
            E_NOTICE
        );
        // @codingStandardsIgnoreEnd

        return $this->arrIds;
    }

    /**
     * Set list of specific ids to be retrieved.
     *
     * @param array $arrIds The list of ids to be retrieved.
     *
     * @return ConfigInterface
     */
    public function setIds($arrIds)
    {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'The method setids in the DefaultConfig is deprecated since 2.1 and will be removed in 3.0. 
            Use set filter 
            $dataConfig->setFilter([[\'operation\' => \'IN\', \'property\' => \'id\', \'values\' => [4,3,2,1]]]).',
            E_NOTICE
        );
        // @codingStandardsIgnoreEnd

        $this->arrIds = $arrIds;

        return $this;
    }

    /**
     * Return flag if only ids should be returned.
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIdOnly()
    {
        return $this->blnIdOnly;
    }

    /**
     * Set flag for return id only.
     *
     * @param boolean $idOnly Boolean flag to determine that only Ids shall be returned when calling fetchAll().
     *
     * @return DefaultConfig
     */
    public function setIdOnly($idOnly)
    {
        $this->blnIdOnly = $idOnly;

        return $this;
    }

    /**
     * Get the offset to start with.
     *
     * This is the offset to use for pagination.
     *
     * @return integer
     */
    public function getStart()
    {
        return $this->intStart;
    }

    /**
     * Set the offset to start with.
     *
     * This is the offset to use for pagination.
     *
     * @param integer $start Number of first element to return.
     *
     * @return ConfigInterface
     */
    public function setStart($start)
    {
        $this->intStart = $start;

        return $this;
    }

    /**
     * Get the limit for results.
     *
     * This is the amount of items to return for pagination.
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->intAmount;
    }

    /**
     * Set the limit for results.
     *
     * This is the amount of items to return for pagination.
     *
     * @param int $amount The amount to use.
     *
     * @return ConfigInterface
     */
    public function setAmount($amount)
    {
        $this->intAmount = $amount;

        return $this;
    }

    /**
     * Get the list with filter options.
     *
     * @return null|array
     */
    public function getFilter()
    {
        return $this->arrFilter;
    }

    /**
     * Set the list with filter options.
     *
     * @param array $filters The array containing the filter values.
     *
     * @return ConfigInterface
     */
    public function setFilter($filters)
    {
        $this->arrFilter = $filters;

        return $this;
    }

    /**
     * Get the list of all defined sortings.
     *
     * The returning array will be of 'property name' => 'ASC|DESC' nature.
     *
     * @return array
     */
    public function getSorting()
    {
        return $this->arrSorting;
    }

    /**
     * Set the list of all defined sortings.
     *
     * The array must be of 'property name' => 'ASC|DESC' nature.
     *
     * @param array $sortingProperties The sorting array to use.
     *
     * @return ConfigInterface
     */
    public function setSorting($sortingProperties)
    {
        $this->arrSorting = $sortingProperties;

        return $this;
    }

    /**
     * Get the list of fields to be retrieved.
     *
     * @return null|array
     */
    public function getFields()
    {
        return $this->arrFields;
    }

    /**
     * Set the list of fields to be retrieved.
     *
     * @param array $fields Array of property names.
     *
     * @return ConfigInterface
     */
    public function setFields($fields)
    {
        $this->arrFields = $fields;

        return $this;
    }

    /**
     * Get the additional information.
     *
     * @param string $informationName The name of the information to retrieve.
     *
     * @return mixed || null
     */
    public function get($informationName)
    {
        if (isset($this->arrData[$informationName])) {
            return $this->arrData[$informationName];
        }

        return null;
    }

    /**
     * Set the additional information.
     *
     * @param string $informationName The name of the information to retrieve.
     * @param mixed  $value           The value to store.
     *
     * @return ConfigInterface
     */
    public function set($informationName, $value)
    {
        $this->arrData[$informationName] = $value;

        return $this;
    }
}
