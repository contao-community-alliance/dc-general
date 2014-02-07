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

namespace DcGeneral\Panel;

use DcGeneral\Data\ConfigInterface;

/**
 * this interface describes a panel.
 *
 * A panel is a row of a panel container.
 *
 * @package DcGeneral\Panel
 */
interface PanelInterface
	extends \IteratorAggregate
{
	/**
	 * Get the parenting container.
	 *
	 * @return PanelContainerInterface
	 */
	public function getContainer();

	/**
	 * Set the parenting container.
	 *
	 * @param PanelContainerInterface $objContainer The Container to be used as parent.
	 *
	 * @return PanelInterface
	 */
	public function setContainer(PanelContainerInterface $objContainer);

	/**
	 * Add an element to the panel.
	 *
	 * @param string                $strKey     Name of the panel.
	 *
	 * @param PanelElementInterface $objElement The element instance to add.
	 *
	 * @return mixed
	 */
	public function addElement($strKey, $objElement);

	/**
	 * Retrieve an element with the given name.
	 *
	 * @param string $strKey The name of the element.
	 *
	 * @return PanelElementInterface
	 */
	public function getElement($strKey);

	/**
	 * Initialize the passed config via all contained elements.
	 *
	 * @param ConfigInterface       $objConfig  The config to which the initialization shall be applied to.
	 *
	 * @param PanelElementInterface $objElement The element to be initialized (if any).
	 *
	 * @return void
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null);
}
