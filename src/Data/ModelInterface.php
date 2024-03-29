<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Interface ModelInterface.
 *
 * This interface describes a model used in data providers.
 *
 * @extends \IteratorAggregate<string, mixed>
 */
interface ModelInterface extends \IteratorAggregate
{
    /**
     * Name of the parent provider.
     */
    public const PARENT_PROVIDER_NAME = 'ptable';

    /**
     * Id value of the parent model.
     */
    public const PARENT_ID = 'pid';

    /**
     * State if we have children.
     */
    public const HAS_CHILDREN = 'dc_gen_tv_children';

    /**
     * If the children shall be shown (i.e. unfolded in tree mode).
     */
    public const SHOW_CHILDREN = 'dc_gen_tv_open';

    /**
     * All child collections.
     */
    public const CHILD_COLLECTIONS = 'dc_gen_children_collection';

    /**
     * Meta name for the model operation buttons.
     */
    public const OPERATION_BUTTONS = '%buttons%';

    /**
     * Meta name for the model label arguments.
     */
    public const LABEL_ARGS = '%args%';

    /**
     * Meta name for the model label (sprintf string).
     */
    public const LABEL_VALUE = '%content%';

    /**
     * Meta name for the model group header.
     */
    public const GROUP_HEADER = '%header%';

    /**
     * Meta name for the model group value.
     */
    public const GROUP_VALUE = '%group%';

    /**
     * Meta name for the model label class.
     */
    public const CSS_CLASS = '%class%';

    /**
     * Meta name for the model label class.
     */
    public const CSS_ROW_CLASS = '%rowClass%';

    /**
     * State if the model is changed
     */
    public const IS_CHANGED = 'isChanged';

    /**
     * Copy this model, without the id.
     *
     * @return void
     */
    public function __clone();

    /**
     * Get the id for this model.
     *
     * @return mixed The Id for this model.
     */
    public function getId();

    /**
     * Fetch the property with the given name from the model.
     *
     * This method returns null if an unknown property is retrieved.
     *
     * @param string $propertyName The property name to be retrieved.
     *
     * @return mixed The value of the given property.
     */
    public function getProperty($propertyName);

    /**
     * Fetch all properties from the model as a name => value array.
     *
     * @return array
     */
    public function getPropertiesAsArray();

    /**
     * Fetch meta information from model.
     *
     * @param string $strMetaName The meta information to retrieve.
     *
     * @return mixed The set meta information or null if undefined.
     */
    public function getMeta($strMetaName);

    /**
     * Set the id for this object.
     *
     *  NOTE: when the ID has been set once to a non-null value, it can NOT be changed anymore.
     *
     *  Normally this should only be called from inside the implementing provider.
     *
     * @param mixed $mixId Could be integer, string or anything else - depends on the provider implementation.
     *
     * @return void
     */
    public function setId($mixId);

    /**
     * Update the property value in the model.
     *
     * @param string $strPropertyName The property name to be set.
     * @param mixed  $varValue        The value to be set.
     *
     * @return void
     */
    public function setProperty($strPropertyName, $varValue);

    /**
     * Update all properties in the model.
     *
     * @param array $properties The property values as name => value pairs.
     *
     * @return void
     */
    public function setPropertiesAsArray($properties);

    /**
     * Update meta information in the model.
     *
     * @param string $strMetaName The meta information name.
     * @param mixed  $varValue    The meta information value to store.
     *
     * @return void
     */
    public function setMeta($strMetaName, $varValue);

    /**
     * Check if this model have any properties.
     *
     * @return boolean true if any property has been stored, false otherwise.
     */
    public function hasProperties();

    /**
     * Return the data provider name.
     *
     * @return string the name of the corresponding data provider.
     */
    public function getProviderName();

    /**
     * Read all values from a value bag.
     *
     * If the value is not present in the value bag, it will get skipped.
     *
     * If the value for a property in the bag is invalid, an exception will get thrown.
     *
     * @param PropertyValueBagInterface $valueBag The value bag where to read from.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralInvalidArgumentException When a property in the value bag has been marked as invalid.
     */
    public function readFromPropertyValueBag(PropertyValueBagInterface $valueBag);

    /**
     * Write values to a value bag.
     *
     * @param PropertyValueBagInterface $valueBag The value bag where to write to.
     *
     * @return ModelInterface
     */
    public function writeToPropertyValueBag(PropertyValueBagInterface $valueBag);
}
