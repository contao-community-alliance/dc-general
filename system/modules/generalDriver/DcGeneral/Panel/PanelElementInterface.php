<?php

namespace DcGeneral\Panel;

use DcGeneral\Data\ConfigInterface;

interface PanelElementInterface
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
	 * @return PanelElementInterface
	 */
	public function setPanel(Panel $objPanel);

	/**
	 *
	 *
	 * @param ConfigInterface  $objConfig The config to which the initialization shall be applied to.
	 *
	 * @param PanelElementInterface $objElement The element to be initialized (if any).
	 *
	 * @return void
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null);

	/**
	 * Render the element using the given Template
	 *
	 * @param PanelTemplateInterface $objTemplate The Template to use.
	 *
	 * @return PanelElementInterface
	 */
	public function render(PanelTemplateInterface $objTemplate);
}
