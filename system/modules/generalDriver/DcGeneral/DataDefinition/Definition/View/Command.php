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

class Command implements CommandInterface
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var \ArrayObject
	 */
	protected $parameters;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var \ArrayObject
	 */
	protected $extra;

	function __construct()
	{
		$this->parameters = new \ArrayObject();
		$this->extra      = new \ArrayObject();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setParameters(\ArrayObject $parameters)
	{
		$this->parameters = $parameters;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLabel($label)
	{
		$this->label = (string) $label;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDescription($description)
	{
		$this->description = (string) $description;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setExtra(\ArrayObject $extra)
	{
		$this->extra = $extra;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getExtra()
	{
		return $this->extra;
	}
}
