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

namespace DcGeneral\Contao\Dca\Definition;

use DcGeneral\DataDefinition\Definition\DefinitionInterface;

class ExtendedDca implements DefinitionInterface
{
	const NAME = 'extended-dca';

	/**
	 * Callback class to use.
	 *
	 * NOTE: Callbacks are deprecated and only executed via the compatibility event \DcGeneral\Event\Subscriber.
	 *
	 * @var string
	 */
	protected $callbackClass;

	/**
	 * Controller class to use.
	 *
	 * @var string
	 */
	protected $controllerClass;

	/**
	 * View class to use.
	 *
	 * @var string
	 */
	protected $viewClass;

	/**
	 * @param string $callbackClass
	 */
	public function setCallbackClass($callbackClass)
	{
		$this->callbackClass = $callbackClass;
	}

	/**
	 * @return string
	 */
	public function getCallbackClass()
	{
		return $this->callbackClass;
	}

	/**
	 * @param string $controllerClass
	 */
	public function setControllerClass($controllerClass)
	{
		$this->controllerClass = $controllerClass;
	}

	/**
	 * @return string
	 */
	public function getControllerClass()
	{
		return $this->controllerClass;
	}

	/**
	 * @param string $viewClass
	 */
	public function setViewClass($viewClass)
	{
		$this->viewClass = $viewClass;
	}

	/**
	 * @return string
	 */
	public function getViewClass()
	{
		return $this->viewClass;
	}
}
