<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use Exception;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

use function array_shift;
use function call_user_func_array;
use function class_exists;
use function count;
use function func_get_args;
use function get_class;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
use function strpos;

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
        $args = func_get_args();
        // But skip $callback.
        array_shift($args);

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
     * @throws ResponseException         Throw the response exception.
     * @throws DcGeneralRuntimeException When the callback throws an exception.
     */
    public static function callArgs($callback, array $args = [])
    {
        try {
            $callback = static::evaluateCallback($callback);

            return call_user_func_array($callback, $args);
        } catch (ResponseException $e) {
            throw $e;
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        if (is_array($callback) && is_object($callback[0])) {
            $callback[0] = get_class($callback[0]);
        }

        throw new DcGeneralRuntimeException(
            sprintf(
                'Execute callback %s failed - Exception message: %s',
                (is_array($callback)
                    ? implode('::', $callback)
                    : (is_string($callback)
                        ? $callback
                        : get_class($callback)
                    )
                ),
                $message
            ),
            0,
            $e
        );
    }

    /**
     * Evaluate the callback and create an object instance if required and possible.
     *
     * @param array|array{0: class-string|string, 1: string}|callable $callback The callback to invoke.
     *
     * @return array|callable
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected static function evaluateCallback($callback)
    {
        if (is_array($callback) && (2 === count($callback)) && is_string($callback[0]) && is_string($callback[1])) {
            $serviceCallback = static::evaluateServiceCallback($callback);
            if ($serviceCallback[0] !== $callback[0]) {
                return $serviceCallback;
            }
            if (!\class_exists($callback[0])) {
                return $callback;
            }

            $class = new ReflectionClass($callback[0]);

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

    /**
     * Evaluate the callback from the service container.
     *
     * @param array $callback The callback.
     *
     * @return array
     *
     * @throws ServiceNotFoundException When service is not public or removed.
     */
    private static function evaluateServiceCallback($callback)
    {
        $container = System::getContainer();

        if (
            $container->has($callback[0])
            && ((false !== strpos($callback[0], '\\')) || !class_exists($callback[0]))
        ) {
            $callback[0] = $container->get($callback[0]);

            return $callback;
        }

        if ($container instanceof Container && isset($container->getRemovedIds()[$callback[0]])) {
            throw new ServiceNotFoundException(
                $callback[0],
                null,
                null,
                [],
                sprintf(
                    'The "%s" service or alias has been removed or inlined when the container was compiled. ' .
                    'You should either make it public, ' .
                    'or stop using the container directly and use dependency injection instead.',
                    $callback[0]
                )
            );
        }

        return $callback;
    }
}
