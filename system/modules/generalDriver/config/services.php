<?php

/** @var Pimple $container */

/**
 * @return \DcGeneral\DataDefinitionContainerInterface $container
 */
$container['dc-general.data-definition-container.factory.default'] = $container->protect(
	function() {
		return new \DcGeneral\DataDefinitionContainer();
	}
);

if (!isset($container['dc-general.data-definition-container.factory'])) {
	$container['dc-general.data-definition-container.factory'] = $container->raw('dc-general.data-definition-container.factory.default');
}

$container['dc-general.data-definition-container'] = $container->share(
	function ($container) {
		$factory      = $container['dc-general.data-definition-container.factory'];
		/** @var \DcGeneral\DataDefinitionContainerInterface $container */
		$container = $factory();

		return $container;
	}
);
