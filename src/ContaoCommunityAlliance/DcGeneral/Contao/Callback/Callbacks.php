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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class Callbacks.
 *
 * Static callback emitting class.
 *
 * @package DcGeneral\Contao\Callback
 */
class Callbacks
{
	/**
	 * Call a Contao style callback.
	 *
	 * @param array|callable $callback The callback to invoke.
	 *
	 * @param mixed          $_        List of arguments to pass to the callback [optional].
	 *
	 * @return mixed
	 */
	public static function call($callback, $_ = null)
	{
		// Get method parameters as callback parameters.
		$args = func_get_args();
		// But skip $callback.
		array_shift($args);

		return static::callArgs($callback, $args);
	}

	/**
	 * Call a Contao style callback.
	 *
	 * @param array|callable $callback The callback to invoke.
	 *
	 * @param array          $args     List of arguments to pass to the callback.
	 *
	 * @return mixed
	 *
	 * @throws DcGeneralRuntimeException When the callback throws an exception.
	 */
	public static function callArgs($callback, array $args = array())
	{
		try {
			$callback = static::evaluateCallback($callback);

			return call_user_func_array($callback, $args);
		}
		catch(\Exception $e) {
			$message = $e->getMessage();
		}

		if (is_array($callback) && is_object($callback[0]))
		{
			$callback[0] = get_class($callback[0]);
		}

		throw new DcGeneralRuntimeException(
			sprintf(
				'Execute callback %s failed - Exception message: %s',
				(is_array($callback) ? implode('::', $callback) : (is_string($callback) ? $callback : get_class($callback))),
				$message
			),
			0
		);
	}

	/**
	 * Evaluate the callback and create an object instance if required and possible.
	 *
	 * @param array|callable $callback The callback to invoke.
	 *
	 * @return array|callable
	 */
	protected static function evaluateCallback($callback)
	{
		if (is_array($callback) && count($callback) == 2 && is_string($callback[0]) && is_string($callback[1]))
		{
			$class = new \ReflectionClass($callback[0]);

			// Ff the method is static, do not create an instance.
			if ($class->hasMethod($callback[1]) && $class->getMethod($callback[1])->isStatic())
			{
				return $callback;
			}

			// Fetch singleton instance.
			if ($class->hasMethod('getInstance'))
			{
				$getInstanceMethod = $class->getMethod('getInstance');

				if ($getInstanceMethod->isStatic())
				{
					$callback[0] = $getInstanceMethod->invoke(null);
					return $callback;
				}
			}

			// Create a new instance.
			$constructor = $class->getConstructor();

			if (!$constructor || $constructor->isPublic())
			{
				$callback[0] = $class->newInstance();
			}

			// Graceful fallback, to prevent access violation to non-public \Backend::__construct().
			else {
				$callback[0] = $class->newInstanceWithoutConstructor();
				$constructor->setAccessible(true);
				$constructor->invoke($callback[0]);
			}
		}

		return $callback;
	}
}
