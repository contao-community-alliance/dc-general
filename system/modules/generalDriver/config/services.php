<?php

/** @var Pimple $container */

/**
 * This function creates the default data definition container.
 * To override the type, set your own implementation override the value of
 * $container['dc-general.data-definition-container.factory'].
 *
 * @return \DcGeneral\DataDefinitionContainerInterface $container
 */
$container['dc-general.data-definition-container.factory.default'] = $container->protect(
	function() {
		return new \DcGeneral\DataDefinitionContainer();
	}
);

if (!isset($container['dc-general.data-definition-container.factory']))
{
	$container['dc-general.data-definition-container.factory'] =
		$container->raw('dc-general.data-definition-container.factory.default');
}

$container['dc-general.data-definition-container'] = $container->share(
	function ($container) {
		$factory = $container['dc-general.data-definition-container.factory'];

		/** @var \DcGeneral\DataDefinitionContainerInterface $container */
		$container = $factory();

		return $container;
	}
);
