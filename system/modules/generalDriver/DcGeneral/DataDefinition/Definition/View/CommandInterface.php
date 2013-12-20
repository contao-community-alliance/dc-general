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

namespace DcGeneral\DataDefinition\Definition\View;

/**
 * Interface CommandInterface.
 *
 * This interface describes a command that can be applied to a model.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
interface CommandInterface
{
	/**
	 * Set the name of the command.
	 *
	 * @param string $name The name of the command.
	 *
	 * @return CommandInterface
	 */
	public function setName($name);

	/**
	 * Return the name of the command.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Set the action properties of the command.
	 *
	 * @param \ArrayObject $parameters The parameters.
	 *
	 * @return CommandInterface
	 */
	public function setParameters(\ArrayObject $parameters);

	/**
	 * Return the action properties of the command.
	 *
	 * @return \ArrayObject
	 */
	public function getParameters();

	/**
	 * Set the label of the command.
	 *
	 * @param string $label The label text.
	 *
	 * @return CommandInterface
	 */
	public function setLabel($label);

	/**
	 * Return the label of the command.
	 *
	 * @return array
	 */
	public function getLabel();

	/**
	 * Set the description of the command.
	 *
	 * @param string $description The description text.
	 *
	 * @return CommandInterface
	 */
	public function setDescription($description);

	/**
	 * Return the description of the command.
	 *
	 * @return array
	 */
	public function getDescription();

	/**
	 * Set extra information.
	 *
	 * @param \ArrayObject $extra The extra data.
	 *
	 * @return CommandInterface
	 */
	public function setExtra(\ArrayObject $extra);

	/**
	 * Fetch extra information.
	 *
	 * @return \ArrayObject
	 */
	public function getExtra();

	/**
	 * Set the command enabled or disabled (true means disabled).
	 *
	 * @param boolean $disabled The flag.
	 *
	 * @return $this
	 */
	public function setDisabled($disabled = true);

	/**
	 * Determine if the command is disabled.
	 *
	 * @return boolean
	 */
	public function isDisabled();
}
