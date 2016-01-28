<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
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
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition;

/**
 * This interface describes the basic information about the data definition.
 */
interface BasicDefinitionInterface extends DefinitionInterface
{
    /**
     * The name of the definition.
     */
    const NAME = 'basic';

    /**
     * Flat mode. All models are on the same hierarchical level. No root conditions are defined.
     */
    const MODE_FLAT = 0;

    /**
     * Hierarchical mode. The models span over various levels.
     */
    const MODE_PARENTEDLIST = 1;

    /**
     * Hierarchical mode. The models span over various levels.
     */
    const MODE_HIERARCHICAL = 2;

    /**
     * Set the mode the data definition is in.
     *
     * @param int $mode The mode value.
     *
     * See the constants in this interface. The mode should either be {@link BasicDefinitionInterface::MODE_FLAT}
     * or {@link BasicDefinitionInterface::MODE_HIERARCHICAL}.
     *
     * @return BasicDefinitionInterface
     */
    public function setMode($mode);

    /**
     * Get the mode the data definition is in.
     *
     * @return int
     */
    public function getMode();

    /**
     * Set the name of the data provider that holds the models for the root level.
     *
     * Be aware that there may be any number of in-between data sources, depending on the defined
     * {@link ParentChildCondition}s
     *
     * Note: This does only apply when in tree mode or parenting mode. For flat mode this does not make sense.
     *
     * @param string $providerName The name of the data provider of the root elements.
     *
     * @return BasicDefinitionInterface
     */
    public function setRootDataProvider($providerName);

    /**
     * Retrieve the name of data provider that holds the models for the root level.
     *
     * Be aware that there may be any number of in-between data sources, depending on the defined
     * {@link ParentChildCondition}s
     *
     * Note: This does only apply when in tree mode or parenting mode. For flat mode this does not make sense.
     *
     * @return string
     */
    public function getRootDataProvider();

    /**
     * Set the name of the data provider that holds the parent model.
     *
     * Note: This does only apply when in tree mode or parenting mode. For flat mode this does not make sense.
     *
     * @param string $providerName The name of the data provider of the parent element.
     *
     * @return BasicDefinitionInterface
     */
    public function setParentDataProvider($providerName);

    /**
     * Retrieve the name of data provider that holds the parent model.
     *
     * Note: This does only apply when in tree mode or parenting mode. For flat mode this does not make sense.
     *
     * @return string
     */
    public function getParentDataProvider();

    /**
     * Set the name of the data provider which holds the models that we work on.
     *
     * @param string $providerName The name of the data provider of the elements being processed.
     *
     * @return BasicDefinitionInterface
     */
    public function setDataProvider($providerName);

    /**
     * Retrieve the name of data provider which holds the models that we work on.
     *
     * @return string
     */
    public function getDataProvider();

    /**
     * Set the additional filters to be used for retrieving elements for the view.
     *
     * @param string $dataProvider The name of the data provider for which additional filters shall be passed.
     *
     * @param array  $filter       Array of filter rules.
     *
     * @return BasicDefinitionInterface
     */
    public function setAdditionalFilter($dataProvider, $filter);

    /**
     * Determine if additional filters are set for the given data provider.
     *
     * @param string $dataProvider The name of the data provider for which additional filters shall be checked.
     *
     * @return bool
     */
    public function hasAdditionalFilter($dataProvider = null);

    /**
     * Get the additional filters to be used for retrieving elements for the view.
     *
     * @param string $dataProvider The name of the data provider for which additional filters shall be retrieved.
     *
     * @return array
     */
    public function getAdditionalFilter($dataProvider = null);

    /**
     * If true, only the edit mode will be shown.
     *
     * This is more or less the opposite to BasicDefinitionInterface::isEditable().
     *
     * @param bool $value The flag - true means that only the edit mode will shown, irrespective of the given action.
     *
     * @return BasicDefinitionInterface
     */
    public function setEditOnlyMode($value);

    /**
     * Boolean flag determining if this data container is in edit only mode.
     *
     * True means, that only the edit mode will shown, irrespective of the given action.
     *
     * @return bool
     */
    public function isEditOnlyMode();

    /**
     * Boolean flag determining if this data container is editable.
     *
     * This is more or less the opposite to BasicDefinitionInterface::isEditOnlyMode().
     *
     * @param bool $value True means, the data records may be edited.
     *
     * @return BasicDefinitionInterface
     */
    public function setEditable($value);

    /**
     * Boolean flag determining if this data container is editable.
     *
     * True means, the data records may be edited.
     *
     * @return bool
     */
    public function isEditable();

    /**
     * Set boolean flag determining if this data container is deletable.
     *
     * @param bool $value True means, the data records may be deleted.
     *
     * @return BasicDefinitionInterface
     */
    public function setDeletable($value);

    /**
     * Boolean flag determining if this data container is deletable.
     *
     * True means, the data records may be deleted.
     *
     * @return bool
     */
    public function isDeletable();

    /**
     * Determines if new entries may be created within this data container.
     *
     * @param bool $value True means new entries may be created, false prohibits creation of new entries.
     *
     * @return BasicDefinitionInterface
     */
    public function setCreatable($value);

    /**
     * Determines if new entries may be created within this data container.
     *
     * True means new entries may be created, false prohibits creation of new entries.
     *
     * @return bool
     */
    public function isCreatable();

    /**
     * Determines if the view shall switch automatically into edit mode.
     *
     * This most likely only affects parenting modes like trees etc.
     *
     * @param bool $switchToEditEnabled The flag - true means switch automatically to editing mode, false allows
     *                                  listing.
     *
     * @return BasicDefinitionInterface
     */
    public function setSwitchToEditEnabled($switchToEditEnabled);

    /**
     * Determines if the view shall switch automatically into edit mode.
     *
     * This most likely only affects parenting modes like trees etc.
     *
     * @return bool
     */
    public function isSwitchToEditEnabled();

    /**
     * Set the ids of the root elements (only valid when in hierarchical mode).
     *
     * The here specified elements will filter the root condition.
     *
     * @param mixed[] $entries The ids of the items to be used as root elements.
     *
     * @return BasicDefinitionInterface
     */
    public function setRootEntries($entries);

    /**
     * Get the ids of the root elements (only valid when in hierarchical mode).
     *
     * @return mixed[]
     */
    public function getRootEntries();
}
