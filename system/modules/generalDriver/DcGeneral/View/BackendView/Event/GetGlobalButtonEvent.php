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

namespace DcGeneral\View\BackendView\Event;

class GetGlobalButtonEvent
	extends BaseButtonEvent
{
    const NAME = 'dc-general.view.widget.get-global-button';

	/**
	 * @var string
	 */
	protected $accessKey;

	/**
	 * @var string
	 */
	protected $class;

	/**
	 * @var string
	 */
	protected $href;

	/**
	 * @param string $accessKey
	 *
	 * @return $this
	 */
	public function setAccessKey($accessKey)
	{
		$this->accessKey = $accessKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAccessKey()
	{
		return $this->accessKey;
	}

	/**
	 * @param string $class
	 *
	 * @return $this
	 */
	public function setClass($class)
	{
		$this->class = $class;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @param string $href
	 *
	 * @return $this
	 */
	public function setHref($href)
	{
		$this->href = $href;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHref()
	{
		return $this->href;
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
}
