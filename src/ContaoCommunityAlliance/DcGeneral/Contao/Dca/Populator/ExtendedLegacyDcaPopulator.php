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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Definition\ExtendedDca;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;

/**
 * Class ExtendedLegacyDcaPopulator.
 *
 * This class only populates the environment with the extended information available via the ExtendedDca data definition
 * section.
 *
 * @package DcGeneral\Contao\Dca\Populator
 */
class ExtendedLegacyDcaPopulator extends AbstractEventDrivenEnvironmentPopulator
{
    const PRIORITY = 100;

    /**
     * Create a view instance in the environment if none has been defined yet.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     *
     * @internal
     */
    protected function populateView(EnvironmentInterface $environment)
    {
        // Already populated, get out then.
        if ($environment->getView())
        {
            return;
        }

        $definition = $environment->getDataDefinition();

        // If we encounter an extended definition, that one may override.
        if (!$definition->hasDefinition(ExtendedDca::NAME))
        {
            return;
        }

        /** @var ExtendedDca $extendedDefinition */
        $extendedDefinition = $definition->getDefinition(ExtendedDca::NAME);
        $class              = $extendedDefinition->getViewClass();

        if (!$class)
        {
            return;
        }

        $viewClass = new \ReflectionClass($class);

        /** @var ViewInterface $view */
        $view = $viewClass->newInstance();

        $view->setEnvironment($environment);
        $environment->setView($view);
    }

    /**
     * Create a controller instance in the environment if none has been defined yet.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     *
     * @internal
     */
    public function populateController(EnvironmentInterface $environment)
    {
        // Already populated, get out then.
        if ($environment->getController())
        {
            return;
        }

        $definition = $environment->getDataDefinition();

        // If we encounter an extended definition, that one may override.
        if (!$definition->hasDefinition(ExtendedDca::NAME))
        {
            return;
        }

        /** @var ExtendedDca $extendedDefinition */
        $extendedDefinition = $definition->getDefinition(ExtendedDca::NAME);
        $class              = $extendedDefinition->getControllerClass();

        if (!$class)
        {
            return;
        }

        $controllerClass = new \ReflectionClass($class);

        /** @var ControllerInterface $controller */
        $controller = $controllerClass->newInstance();

        $controller->setEnvironment($environment);
        $environment->setController($controller);
    }

    /**
     * {@inheritDoc}
     */
    public function populate(EnvironmentInterface $environment)
    {
        $this->populateView($environment);
        $this->populateController($environment);
    }
}
