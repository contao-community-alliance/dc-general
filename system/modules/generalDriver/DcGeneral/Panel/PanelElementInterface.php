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
use DcGeneral\View\ViewTemplateInterface;

/**
 * A simple element contained within a panel.
 *
 * @package DcGeneral\Panel
 */
interface PanelElementInterface
{
	/**
	 * Return the parenting panel.
	 *
	 * @return PanelInterface
	 */
	public function getPanel();

	/**
	 * Return the parenting panel.
	 *
	 * @param PanelInterface $objPanel The panel to use as parent.
	 *
	 * @return PanelElementInterface
	 */
	public function setPanel(PanelInterface $objPanel);

	/**
	 * Initialize the passed configuration with the values of the element.
	 *
	 * @param ConfigInterface       $objConfig  The config to which the initialization shall be applied to.
	 *
	 * @param PanelElementInterface $objElement The element to be initialized (if any).
	 *
	 * @return void
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null);

	/**
	 * Render the element using the given Template.
	 *
	 * @param ViewTemplateInterface $objTemplate The Template to use.
	 *
	 * @return PanelElementInterface
	 */
	public function render(ViewTemplateInterface $objTemplate);
}
