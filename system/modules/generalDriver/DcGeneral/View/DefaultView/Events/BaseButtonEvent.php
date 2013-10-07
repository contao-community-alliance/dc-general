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

namespace DcGeneral\View\DefaultView\Events;

use DcGeneral\Events\BaseEvent;

class BaseButtonEvent
	extends BaseEvent
{
	/**
	 * The html attributes to use for the button.
	 *
	 * @var string
	 */
	protected $attributes;

	/**
	 * The Html code to use for this button.
	 *
	 * @var string
	 */
	protected $html;

	/**
	 * The key of the button.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The label to use for the button.
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * The title to use for the button.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * @param string $attributes
	 *
	 * @return $this
	 */
	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * @param string $html
	 *
	 * @return $this
	 */
	public function setHtml($html)
	{
		$this->html = $html;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHtml()
	{
		return $this->html;
	}

	/**
	 * @param string $key
	 *
	 * @return $this
	 */
	public function setKey($key)
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $label
	 *
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->label = $label;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @param string $title
	 *
	 * @return $this
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
}
