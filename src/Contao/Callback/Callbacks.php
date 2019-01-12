<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class Callbacks.
 *
 * Static callback emitting class.
 */
class Callbacks
{
    /**
     * Call a Contao style callback.
     *
     * @param array|callable $callback The callback to invoke.
     * @param mixed          $_        List of arguments to pass to the callback [optional].
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CamelCaseParameterName)
     */
    public static function call($callback, $_ = null)
    {
        // Get method parameters as callback parameters.
        $args = \func_get_args();
        // But skip $callback.
        \array_shift($args);

        return static::callArgs($callback, $args);
    }

    /**
     * Call a Contao style callback.
     *
     * @param array|callable $callback The callback to invoke.
     * @param array          $args     List of arguments to pass to the callback.
     *
     * @return mixed
     *
     * @throws DcGeneralRuntimeException When the callback throws an exception.
     */
    public static function callArgs($callback, array $args = [])
    {
        try {
            $callback = static::evaluateCallback($callback);

            return \call_user_func_array($callback, $args);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        if (\is_array($callback) && \is_object($callback[0])) {
            $callback[0] = \get_class($callback[0]);
        }

        throw new DcGeneralRuntimeException(
            \sprintf(
                'Execute callback %s failed - Exception message: %s',
                (\is_array($callback) ? \implode('::', $callback) : (\is_string($callback) ? $callback : \get_class(
                    $callback
                ))),
                $message
            ),
            0,
            $e
        );
    }

    /**
     * Evaluate the callback and create an object instance if required and possible.
     *
     * @param array|callable $callback The callback to invoke.
     *
     * @return array|callable
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected static function evaluateCallback($callback)
    {
        if (\is_array($callback) && \count($callback) == 2 && \is_string($callback[0]) && \is_string($callback[1])) {
            $class = new \ReflectionClass($callback[0]);

            // Ff the method is static, do not create an instance.
            if ($class->hasMethod($callback[1]) && $class->getMethod($callback[1])->isStatic()) {
                return $callback;
            }

            // Fetch singleton instance.
            if ($class->hasMethod('getInstance')) {
                $getInstanceMethod = $class->getMethod('getInstance');

                if ($getInstanceMethod->isStatic()) {
                    $callback[0] = $getInstanceMethod->invoke(null);
                    return $callback;
                }
            }

            // Create a new instance.
            $constructor = $class->getConstructor();

            if (!$constructor || $constructor->isPublic()) {
                $callback[0] = $class->newInstance();
            } else {
                // Graceful fallback, to prevent access violation to non-public \Backend::__construct().
                $callback[0] = $class->newInstanceWithoutConstructor();
                $constructor->setAccessible(true);
                $constructor->invoke($callback[0]);
            }
        }

        return $callback;
    }
}
