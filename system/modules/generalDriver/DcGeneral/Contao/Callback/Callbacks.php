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

namespace DcGeneral\Contao\Callback;

use DcGeneral\Exception\DcGeneralRuntimeException;

class Callbacks
{
	/**
	 * Call a Contao style callback.
	 *
	 * @param array|callable $callback
	 * @param mixed $_
	 */
	static public function call($callback, $_ = null)
	{
		// get method parameters as callback parameters
		$args = func_get_args();
		// ... but skip $callback
		array_shift($args);

		return static::callArgs($callback, $args);
	}

	/**
	 * Call a Contao style callback.
	 *
	 * @param array|callable $callback
	 * @param array $args
	 */
	static public function callArgs($callback, array $args = array())
	{
		try {
			$callback = static::evaluateCallback($callback);

			return call_user_func_array($callback, $args);
		}
		catch(\Exception $e) {
			throw new DcGeneralRuntimeException(
				'Execute callback ' . (is_array($callback) ? implode('::', $callback) : $callback) . ' failed',
				0,
				$e
			);
		}
	}

	/**
	 * Evaluate the callback and create an object instance if required and possible.
	 *
	 * @param array|callable $callback
	 *
	 * @return array|callable
	 */
	static protected function evaluateCallback($callback)
	{
		if (is_array($callback) && count($callback) == 2 && is_string($callback[0]) && is_string($callback[1]))
		{
			$class = new \ReflectionClass($callback[0]);

			// if the method is static, do not create an instance
			if ($class->hasMethod($callback[1]) && $class->getMethod($callback[1])->isStatic()) {
				return $callback;
			}

			// fetch singleton instance
			if ($class->hasMethod('getInstance')) {
				$getInstanceMethod = $class->getMethod('getInstance');

				if ($getInstanceMethod->isStatic()) {
					$callback[0] = $getInstanceMethod->invoke(null);
					return $callback;
				}
			}

			// create a new instance
			$callback[0] = $class->newInstance();
		}

		return $callback;
	}
}
