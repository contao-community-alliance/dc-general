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

namespace DcGeneral\View\BackendView;

use DcGeneral\EnvironmentInterface;
use DcGeneral\View\ViewInterface;

interface BackendViewInterface extends ViewInterface
{
	/**
	 * @param \DcGeneral\Panel\PanelContainerInterface $panelContainer
	 *
	 * @return BackendViewInterface
	 */
	public function setPanel($panelContainer);

	/**
	 * @return \DcGeneral\Panel\PanelContainerInterface
	 */
	public function getPanel();
}

