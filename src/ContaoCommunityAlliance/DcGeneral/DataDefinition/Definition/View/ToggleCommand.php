<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Toggle command - special command for toggling a boolean property between '1' and '' (empty string).
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
class ToggleCommand
	extends Command
	implements ToggleCommandInterface
{
	/**
	 * The property name to toggle.
	 *
	 * @var string
	 */
	protected $property;

	/**
	 * {@inheritDoc}
	 */
	public function setToggleProperty($property)
	{
		$this->property = $property;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getToggleProperty()
	{
		return $this->property;
	}
}
