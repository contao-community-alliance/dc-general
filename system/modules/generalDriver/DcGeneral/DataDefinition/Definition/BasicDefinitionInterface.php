<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\Definition;

/**
 * Interface BasicDefinitionInterface
 *
 * @package DcGeneral\DataDefinition\Definition
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
	 * @param int $mode
	 *
	 * See the constants in this interface. The mode should either be {@link BasicDefinitionInterface::MODE_FLAT}
	 * or {@link BasicDefinitionInterface::MODE_HIERARCHICAL}.
	 *
	 * @return BasicDefinitionInterface
	 */
	public function setMode($mode);

	/**
	 * @return int
	 */
	public function getMode();

	/**
	 * Set the name of the data provider that holds the models for the root level.
	 *
	 * Be aware that there may be any number of in-between data sources, depending on the defined {@link ParentChildCondition}s
	 *
	 * Note: This does only apply when in tree mode or parenting mode. For flat mode this does not make sense.
	 *
	 * @param string $providerName
	 *
	 */
	public function setRootDataProvider($providerName);

	/**
	 * Retrieve the name of data provider that holds the models for the root level.
	 *
	 * Be aware that there may be any number of in-between data sources, depending on the defined {@link ParentChildCondition}s
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
	 * @param string $providerName
	 *
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
	 * @param string $providerName
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
	 * Set the filter to be used for retrieving the root elements of the view.
	 *
	 * @param array $filter
	 *
	 * @return BasicDefinitionInterface
	 */
	public function setRootFilter($filter);

	/**
	 * Determine if there has been a root filter set.
	 *
	 * @return bool
	 */
	public function hasRootFilter();

	/**
	 * Get the filter to be used for retrieving the root elements of the view.
	 *
	 * @return array
	 */
	public function getRootFilter();

	/**
	 * If true, adding of further records is prohibited.
	 *
	 * @param bool $value
	 *
	 * @return BasicDefinitionInterface
	 */
	public function setClosed($value);

	/**
	 * Boolean flag determining if this data container is closed.
	 *
	 * True means, there may not be any records added or deleted, false means there may be any record appended or
	 * deleted..
	 *
	 * @return bool
	 */
	public function isClosed();

	/**
	 * Boolean flag determining if this data container is editable.
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
	 * This most likely only affects parenting modes like trees etc.
	 *
	 * @param bool $switchToEditEnabled
	 *
	 * @return BasicDefinitionInterface
	 */
	public function setSwitchToEditEnabled($switchToEditEnabled);

	/**
	 * Determines if the view shall switch automatically into edit mode.
	 * This most likely only affects parenting modes like trees etc.
	 *
	 * @return bool
	 */
	public function isSwitchToEditEnabled();
}
