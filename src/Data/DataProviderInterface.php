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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Interface DataProviderInterface.
 *
 * This interface describes a data provider in DcGeneral.
 */
interface DataProviderInterface
{
    /**
     * Set base config with source and other necessary parameter.
     *
     * @param array $config The configuration to use.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When no source has been defined.
     */
    public function setBaseConfig(array $config);

    /**
     * Return an empty configuration object.
     *
     * @return ConfigInterface
     */
    public function getEmptyConfig();

    /**
     * Fetch an empty single record (new model).
     *
     * @return ModelInterface
     */
    public function getEmptyModel();

    /**
     * Fetch an empty single collection (new model list).
     *
     * @return CollectionInterface
     */
    public function getEmptyCollection();

    /**
     * Fetch a single or first record by id or filter.
     *
     * If the model shall be retrieved by id, use $objConfig->setId() to populate the config with an Id.
     *
     * If the model shall be retrieved by filter, use $objConfig->setFilter() to populate the config with a filter.
     *
     * @param ConfigInterface $config The configuration to use.
     *
     * @return ModelInterface
     */
    public function fetch(ConfigInterface $config);

    /**
     * Fetch all records (optional filtered, sorted and limited).
     *
     * This returns a collection of all models matching the config object. If idOnly is true, an array containing all
     * matching ids is returned.
     *
     * @param ConfigInterface $config The configuration to use.
     *
     * @return CollectionInterface|ModelInterface[]|string[]
     */
    public function fetchAll(ConfigInterface $config);

    /**
     * Retrieve all unique values for the given property.
     *
     * The result set will be an array containing all unique values contained in the data provider.
     * Note: this only re-ensembles really used values for at least one data set.
     *
     * The only information being interpreted from the passed config object is the first property to fetch and the
     * filter definition.
     *
     * @param ConfigInterface $config The filter config options.
     *
     * @return FilterOptionCollectionInterface
     */
    public function getFilterOptions(ConfigInterface $config);

    /**
     * Return the amount of total items (filtering may be used in the config).
     *
     * @param ConfigInterface $config The configuration to use.
     *
     * @return int
     */
    public function getCount(ConfigInterface $config);

    /**
     * Save an item to the data provider.
     *
     * If the item does not have an Id yet, the save operation will add it as a new row to the database and
     * populate the Id of the model accordingly.
     *
     * @param ModelInterface $item      The model to save back.
     * @param int            $timestamp Optional parameter for use own timestamp.
     *                                  This is useful if save a collection of models and all shall have
     *                                  the same timestamp.
     *
     * @return ModelInterface The passed model.
     */
    public function save(ModelInterface $item, $timestamp = 0);

    /**
     * Save a collection of items to the data provider.
     *
     * @param CollectionInterface $items     The collection containing all items to be saved.
     * @param int                 $timestamp Optional parameter for use own timestamp.
     *                                       This is useful if save a collection of models and all shall have
     *                                       the same timestamp.
     *
     * @return void
     */
    public function saveEach(CollectionInterface $items, $timestamp = 0);

    /**
     * Delete an item.
     *
     * The given value may be either integer, string or an instance of Model
     *
     * @param mixed $item Id or the model itself, to delete.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When an unusable object has been passed.
     */
    public function delete($item);

    /**
     * Save a new version of a model.
     *
     * @param ModelInterface $model    The model for which a new version shall be created.
     * @param string         $username The username to attach to the version as creator.
     *
     * @return void
     */
    public function saveVersion(ModelInterface $model, $username);

    /**
     * Return a model based of the version information.
     *
     * @param mixed $mixID      The ID of the record.
     * @param mixed $mixVersion The ID of the version.
     *
     * @return ModelInterface
     */
    public function getVersion($mixID, $mixVersion);

    /**
     * Return a list with all versions for the model with the given Id.
     *
     * @param mixed   $mixID      The ID of the row.
     * @param boolean $onlyActive If true, only active versions will get returned, if false all version will get
     *                            returned.
     *
     * @return CollectionInterface
     */
    public function getVersions($mixID, $onlyActive = false);

    /**
     * Set a version as active.
     *
     * @param mixed $mixID      The ID of the model.
     * @param mixed $mixVersion The version number to set active.
     *
     * @return void
     */
    public function setVersionActive($mixID, $mixVersion);

    /**
     * Retrieve the current active version for a model.
     *
     * @param mixed $mixID The ID of the model.
     *
     * @return mixed The current version number of the requested row.
     */
    public function getActiveVersion($mixID);

    /**
     * Reset the fallback field.
     *
     * This clears the given property in all items in the data provider to an empty value.
     *
     * Documentation:
     *      Evaluation - fallback => If true the field can only be assigned once per table.
     *
     * @param string $field The field to reset.
     *
     * @return void
     *
     * @deprecated Handle the resetting manually as you must filter the models.
     */
    public function resetFallback($field);

    /**
     * Check if the value is unique in the data provider.
     *
     * @param string $field     The field in which to test.
     * @param mixed  $new       The value about to be saved.
     * @param int    $primaryId The (optional) id of the item currently in scope - pass null for new items.
     *
     * Documentation:
     *      Evaluation - unique => If true the field value cannot be saved if it exists already.
     *
     * @return boolean
     */
    public function isUniqueValue($field, $new, $primaryId = null);

    /**
     * Check if a property with the given name exists in the data provider.
     *
     * @param string $columnName The name of the property to search.
     *
     * @return boolean
     */
    public function fieldExists($columnName);

    /**
     * Check if two models have the same values in all properties.
     *
     * @param ModelInterface $firstModel  The first model to compare.
     * @param ModelInterface $secondModel The second model to compare.
     *
     * @return boolean True - If both models are same, false if not.
     */
    public function sameModels($firstModel, $secondModel);
}
