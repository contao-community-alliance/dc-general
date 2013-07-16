<?php

namespace DcGeneral\Panel\Interfaces;

use DcGeneral\Data\Interfaces\Config;

interface Element
{
	/**
	 * Return the parenting panel.
	 *
	 * @return Panel
	 */
	public function getPanel();

	/**
	 * Return the parenting panel.
	 *
	 * @param Panel $objPanel The panel to use as parent.
	 *
	 * @return Element
	 */
	public function setPanel(Panel $objPanel);

	/**
	 *
	 *
	 * @param Config  $objConfig The config to which the initialization shall be applied to.
	 *
	 * @param Element $objElement The element to be initialized (if any).
	 *
	 * @return void
	 */
	public function initialize(Config $objConfig, Element $objElement = null);

	/**
	 * Render the element using the given Template
	 *
	 * @param Template $objTemplate The Template to use.
	 *
	 * @return Element
	 */
	public function render($objTemplate);
}
