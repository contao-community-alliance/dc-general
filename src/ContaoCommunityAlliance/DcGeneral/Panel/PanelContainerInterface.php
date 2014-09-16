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

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This interface describes a panel container.
 *
 * A Panel container contains panels which contain panel elements.
 *
 * @package DcGeneral\Panel
 */
interface PanelContainerInterface extends \IteratorAggregate, \Countable
{
	/**
	 * Get the environment in use.
	 *
	 * @return EnvironmentInterface
	 */
	public function getEnvironment();

	/**
	 * Set the environment in use.
	 *
	 * @param EnvironmentInterface $objEnvironment The environment to use.
	 *
	 * @return PanelContainerInterface
	 */
	public function setEnvironment(EnvironmentInterface $objEnvironment);

	/**
	 * Add a panel to the container.
	 *
	 * @param string         $strKey   Name of the panel.
	 *
	 * @param PanelInterface $objPanel The panel to add.
	 *
	 * @return PanelContainerInterface
	 */
	public function addPanel($strKey, $objPanel);

	/**
	 * Retrieve a panel from the container.
	 *
	 * @param string $strKey The name of the panel.
	 *
	 * @return PanelInterface
	 */
	public function getPanel($strKey);

	/**
	 * Initialize all panels and apply all restrictions to the given Config.
	 *
	 * @param ConfigInterface       $objConfig  The data config to be populated with the element values.
	 *
	 * @param PanelElementInterface $objElement The element currently being initialized.
	 *
	 * @return PanelContainerInterface
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null);

	/**
	 * Determinator if the panels should be updated from the InputProvider or not.
	 *
	 * @return bool
	 */
	public function updateValues();
}
