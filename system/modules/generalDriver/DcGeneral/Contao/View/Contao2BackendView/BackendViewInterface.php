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

namespace DcGeneral\Contao\View\Contao2BackendView;

use DcGeneral\View\ViewInterface;

/**
 * Interface BackendViewInterface.
 *
 * This interface describes extensions over the ViewInterface regarding the Contao 2 Backend view.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView
 */
interface BackendViewInterface extends ViewInterface
{
	/**
	 * Set the panel container.
	 *
	 * @param \DcGeneral\Panel\PanelContainerInterface $panelContainer The panel container.
	 *
	 * @return BackendViewInterface
	 */
	public function setPanel($panelContainer);

	/**
	 * Retrieve the panel container from the view.
	 *
	 * @return \DcGeneral\Panel\PanelContainerInterface
	 */
	public function getPanel();
}

