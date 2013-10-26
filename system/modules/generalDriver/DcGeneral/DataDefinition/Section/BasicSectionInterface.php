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

namespace DcGeneral\DataDefinition\Section;

/**
 * Interface BasicSectionInterface
 *
 * @package DcGeneral\DataDefinition\Section
 */
interface BasicSectionInterface extends ContainerSectionInterface
{
	/**
	 * The name of the section.
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
	 * See the constants in this interface. The mode should either be {@link BasicSectionInterface::MODE_FLAT}
	 * or {@link BasicSectionInterface::MODE_HIERARCHICAL}.
	 *
	 * @return BasicSectionInterface
	 */
	public function setMode($mode);

	/**
	 * @return int
	 */
	public function getMode();

	/**
	 * Set the name of the data provider that holds the models for the root level.
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
	 * Set the name of the data provider which holds the models that we work on.
	 *
	 * @param string $providerName
	 *
	 */
	public function setDataProvider($providerName);

	/**
	 * Retrieve the name of data provider which holds the models that we work on.
	 *
	 * @return string
	 */
	public function getDataProvider();

	/**
	 * @param bool $switchToEditEnabled
	 *
	 * @return ContainerSectionInterface
	 */
	public function setSwitchToEditEnabled($switchToEditEnabled);

	/**
	 * @return bool
	 */
	public function isSwitchToEditEnabled();
}
