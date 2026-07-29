<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\EditOnlyDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\SortElementInterface;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function sprintf;
use function strtoupper;

/**
 * Helper class that provides static methods used in views.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * The class complexity is a property of this collection of static helpers as a whole, not of a
 * single method in it - splitting it would move the sum around without making anything clearer.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @api
 */
class ViewHelpers
{
    /**
     * Retrieve the currently active sorting.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return GroupAndSortingDefinitionInterface|null
     */
    public static function getCurrentSorting(EnvironmentInterface $environment)
    {
        /** @var BackendViewInterface $view */
        $view = $environment->getView();

        $panelInterface = $view->getPanel();
        assert($panelInterface instanceof PanelContainerInterface);

        foreach ($panelInterface as $panel) {
            /** @var PanelInterface $panel */
            $sort = $panel->getElement('sort');
            if ($sort instanceof SortElementInterface) {
                return $sort->getSelectedDefinition();
            }
        }

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        /** @var Contao2BackendViewDefinitionInterface $viewSection */
        $viewSection = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $definition  = $viewSection->getListingConfig()->getGroupAndSortingDefinition();
        if ($definition->hasDefault()) {
            return $definition->getDefault();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @return string|null
     */
    public static function getManualSortingProperty(EnvironmentInterface $environment)
    {
        /** @var BackendViewInterface $view */
        $view = $environment->getView();

        $panelInterface = $view->getPanel();
        assert($panelInterface instanceof PanelContainerInterface);

        $definition = null;
        foreach ($panelInterface as $panel) {
            /** @var PanelInterface $panel */
            $sort = $panel->getElement('sort');
            if ($sort instanceof SortElementInterface) {
                $definition = $sort->getSelectedDefinition();
                break;
            }
        }

        if (null === $definition) {
            $dataDefinition = $environment->getDataDefinition();
            assert($dataDefinition instanceof ContainerInterface);

            /** @var Contao2BackendViewDefinitionInterface $viewDefinition */
            $viewDefinition  = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
            $groupAndSorting = $viewDefinition->getListingConfig()->getGroupAndSortingDefinition();

            if ($groupAndSorting->hasDefault()) {
                $definition = $groupAndSorting->getDefault();
            }
        }

        if ($definition) {
            foreach ($definition as $information) {
                if ($information->isManualSorting()) {
                    return $information->getProperty();
                }
            }
        }

        return null;
    }

    /**
     * Initialize the sorting from the panel. Fallback to default sorting if nothing given.
     *
     * @param PanelContainerInterface $panel         The current panel.
     * @param ConfigInterface         $dataConfig    The current config.
     * @param ListingConfigInterface  $listingConfig The listing config.
     *
     * @return void
     */
    public static function initializeSorting($panel, $dataConfig, $listingConfig)
    {
        // Store default sorting start initializing the panel with an empty sorting.
        $sorting = $dataConfig->getSorting();
        $dataConfig->setSorting([]);
        $panel->initialize($dataConfig);

        // Restore default sorting if panel did not set any.
        if ($sorting && !$dataConfig->getSorting()) {
            $dataConfig->setSorting($sorting);
        }

        // Initialize sorting if not present yet.
        if (!$dataConfig->getSorting() && $listingConfig->getGroupAndSortingDefinition()->hasDefault()) {
            $newSorting = [];
            foreach ($listingConfig->getGroupAndSortingDefinition()->getDefault() as $information) {
                /** @var GroupAndSortingInformationInterface $information */
                $newSorting[$information->getProperty()] = strtoupper($information->getSortingMode());
            }
            $dataConfig->setSorting($newSorting);
        }
    }

    /**
     * Retrieve the currently active grouping mode.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array|null
     */
    public static function getGroupingMode(EnvironmentInterface $environment)
    {
        $sorting = static::getCurrentSorting($environment);

        // If no sorting defined, exit.
        if (
            (!$sorting)
            || (!$sorting->getCount())
            || $sorting->get(0)->getSortingMode() === GroupAndSortingInformationInterface::SORT_RANDOM
        ) {
            return null;
        }
        $firstSorting = $sorting->get(0);

        // Use the information from the property, if given.
        if ('' !== $firstSorting->getGroupingMode()) {
            $groupMode   = $firstSorting->getGroupingMode();
            $groupLength = $firstSorting->getGroupingLength();
        } else {
            // No sorting? No grouping!
            $groupMode   = GroupAndSortingInformationInterface::GROUP_NONE;
            $groupLength = 0;
        }

        return [
            'mode'     => $groupMode,
            'length'   => $groupLength,
            'property' => $firstSorting->getProperty(),
            'sorting'  => $sorting
        ];
    }

    /**
     * Get for a field the readable value.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param PropertyInterface    $property    The property to be rendered.
     * @param ModelInterface       $model       The model from which the property value shall be retrieved from.
     *
     * @return mixed
     */
    public static function getReadableFieldValue(
        EnvironmentInterface $environment,
        PropertyInterface $property,
        ModelInterface $model
    ) {
        $event = new RenderReadablePropertyValueEvent(
            $environment,
            $model,
            $property,
            $model->getProperty($property->getName())
        );

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($event, $event::NAME);

        if (null !== $event->getRendered()) {
            return $event->getRendered();
        }

        return $event->getValue();
    }

    /**
     * Redirects to the real back end module.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return never
     */
    public static function redirectHome(EnvironmentInterface $environment): never
    {
        self::dispatchRedirect($environment, new RedirectEvent(self::getBackUrl($environment)));
    }

    /** @param list<string> $cleanNames */
    public static function redirectCleanHome(EnvironmentInterface $environment, array $cleanNames): never
    {
        self::dispatchRedirect($environment, new RedirectEvent(self::getBackUrl($environment, $cleanNames)));
    }

    /**
     * Build the "back to the list" URL for the current view without redirecting.
     *
     * This deterministically reconstructs the parent list URL from the current request
     * (route + route parameters + query, minus the record/action specific parameters).
     * It replaces the former reliance on the session based referer, which Contao 5.7 no
     * longer maintains.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param list<string>         $cleanNames  Additional parameters to strip from the URL.
     *
     * @return string
     */
    public static function getBackUrl(EnvironmentInterface $environment, array $cleanNames = []): string
    {
        $request   = self::getRequest();
        $routeName = $request->attributes->get('_route');

        // Without an action we are already standing on the list that "table" and "pid" describe - going
        // back then means one level up, to the parent list. With an action (edit, show, select …) those
        // parameters have to survive, because back leads to exactly that list.
        //
        // An edit only table has no list at all - its data provider may even refuse to fetch a
        // collection - so back has to leave the table behind in that case as well, no matter the action.
        if (
            self::isEditOnly($environment)
            || (null === $request->query->get('act') && !$request->attributes->has('act'))
        ) {
            $cleanNames = \array_merge($cleanNames, ['table', 'pid']);
        }

        if (null !== $routeName && 'contao_backend' !== $routeName) {
            return self::buildNewStyleUrl((string) $routeName, $request, $cleanNames);
        }

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        return self::buildLegacyUrl($input, $cleanNames);
    }

    /**
     * Tell whether the data definition is in edit only mode.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private static function isEditOnly(EnvironmentInterface $environment): bool
    {
        $definition = $environment->getDataDefinition();

        // The provider is the more reliable source: the dca flag is easy to forget, while a provider
        // that cannot list knows so.
        return ($definition instanceof ContainerInterface && $definition->getBasicDefinition()->isEditOnlyMode())
            || ($environment->hasDataProvider()
                && $environment->getDataProvider() instanceof EditOnlyDataProviderInterface);
    }

    /** @param list<string> $cleanNames */
    private static function buildNewStyleUrl(string $routeName, Request $request, array $cleanNames): string
    {
        $routeGenerator = System::getContainer()->get('router');
        assert($routeGenerator instanceof UrlGeneratorInterface);

        $parameters = $request->query->all();
        foreach ($cleanNames as $key) {
            unset($parameters[$key]);
        }
        if ($routeName === $request->attributes->get('_route')) {
            /** @var array<string, scalar> $routeParams */
            $routeParams = $request->attributes->get('_route_params') ?? [];
            foreach ($routeParams as $key => $value) {
                if ('_' === $key[0] || \in_array($key, $cleanNames, true)) {
                    continue;
                }
                $parameters[$key] = $value;
            }
        }

        // Strip the parameters that only make sense for the current record and action, so
        // that we land on the plain list view (rt is the request token, id the record id).
        unset($parameters['act'], $parameters['id'], $parameters['rt']);

        return $routeGenerator->generate($routeName, $parameters);
    }

    /** @param list<string> $cleanNames */
    private static function buildLegacyUrl(InputProviderInterface $input, array $cleanNames = []): string
    {
        if ($input->hasParameter('table') && !\in_array('table', $cleanNames, true)) {
            if ($input->hasParameter('pid') && !\in_array('pid', $cleanNames, true)) {
                return sprintf(
                    'contao?do=%s&table=%s&pid=%s',
                    (string) $input->getParameter('do'),
                    (string) $input->getParameter('table'),
                    (string) $input->getParameter('pid')
                );
            }

            return sprintf(
                'contao?do=%s&table=%s',
                (string) $input->getParameter('do'),
                (string) $input->getParameter('table')
            );
        }

        return sprintf('contao?do=%s', (string) $input->getParameter('do'));
    }

    private static function getRequest(): Request
    {
        $requestStack = System::getContainer()->get('request_stack');
        assert($requestStack instanceof RequestStack);

        $request = $requestStack->getCurrentRequest();
        assert($request instanceof Request);

        return $request;
    }

    public static function dispatchRedirect(EnvironmentInterface $environment, RedirectEvent $event): never
    {
        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($event, ContaoEvents::CONTROLLER_REDIRECT);

        throw new LogicException('Redirect did not happen.');
    }
}
