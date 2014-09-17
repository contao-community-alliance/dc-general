<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Interface ConfigInterface.
 *
 * This interface describes the configuration objects to use when retrieving models and collections from a data
 * provider.
 *
 * @package DcGeneral\Data
 */
interface ConfigInterface
{
    /**
     * Static constructor.
     *
     * @todo: do we want to keep this behaviour? Third party will not know the correct class anyway.
     *
     * @return mixed
     */
    public static function init();

    /**
     * Get specific id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set a specific id for an element to be retrieved.
     *
     * @param mixed $mixId The id of the element to be retrieved.
     *
     * @return ConfigInterface
     */
    public function setId($mixId);

    /**
     * Get list of specific ids to be retrieved.
     *
     * @return array
     */
    public function getIds();

    /**
     * Set list of specific ids to be retrieved.
     *
     * @param array $arrIds The list of ids to be retrieved.
     *
     * @return ConfigInterface
     */
    public function setIds($arrIds);

    /**
     * Return flag if only ids should be returned.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIdOnly();

    /**
     * Set flag for return id only.
     *
     * @param bool $blnIdOnly Boolean flag to determine that only Ids shall be returned when calling fetchAll().
     *
     * @return ConfigInterface
     */
    public function setIdOnly($blnIdOnly);

    /**
     * Get the offset to start with.
     *
     * This is the offset to use for pagination.
     *
     * @return int
     */
    public function getStart();

    /**
     * Set the offset to start with.
     *
     * This is the offset to use for pagination.
     *
     * @param int $intStart Number of first element to return.
     *
     * @return ConfigInterface
     */
    public function setStart($intStart);

    /**
     * Get the limit for results.
     *
     * This is the amount of items to return for pagination.
     *
     * @return int
     */
    public function getAmount();

    /**
     * Set the limit for results.
     *
     * This is the amount of items to return for pagination.
     *
     * @param int $intAmount The amount to use.
     *
     * @return ConfigInterface
     */
    public function setAmount($intAmount);

    /**
     * Get the list with filter options.
     *
     * @return array
     */
    public function getFilter();

    /**
     * Set the list with filter options.
     *
     * @param array $arrFilter The array containing the filter values.
     *
     * @return ConfigInterface
     */
    public function setFilter($arrFilter);

    /**
     * Get the list of all defined sortings.
     *
     * The returning array will be of 'property name' => 'ASC|DESC' nature.
     *
     * @return array
     */
    public function getSorting();

    /**
     * Set the list of all defined sortings.
     *
     * The array must be of 'property name' => 'ASC|DESC' nature.
     *
     * @param array $arrSorting The sorting array to use.
     *
     * @return array
     */
    public function setSorting($arrSorting);

    /**
     * Get the list of fields to be retrieved.
     *
     * @return array
     */
    public function getFields();

    /**
     * Set the list of fields to be retrieved.
     *
     * @param array $arrFields Array of property names.
     *
     * @return ConfigInterface
     */
    public function setFields($arrFields);

    // TODO: make a property bag out of this.
    /**
     * Get the additional information.
     *
     * @param string $strKey The name of the information to retrieve.
     *
     * @return mixed || null
     */
    public function get($strKey);

    /**
     * Set the additional information.
     *
     * @param string $strKey   The name of the information to retrieve.
     *
     * @param mixed  $varValue The value to store.
     *
     * @return ConfigInterface
     */
    public function set($strKey, $varValue);
}
