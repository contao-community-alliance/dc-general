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

namespace DcGeneral\DataDefinition\Definition\View;

class DefaultPanelLayout implements PanelLayoutInterface
{
	/**
	 * @var PanelRowCollectionInterface[]
	 */
	protected $rows;

	public function __construct()
	{
		$this->rows = new DefaultPanelRowCollection();
	}

	public function getRows()
	{
		return $this->rows;
	}
}
