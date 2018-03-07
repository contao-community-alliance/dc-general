<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\SortElementInterface;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;

/**
 * Helper class that provides static methods used in views.
 */
class ViewHelpers
{
    /**
     * Retrieve the currently active sorting.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return GroupAndSortingDefinitionInterface
     */
    public static function getCurrentSorting(EnvironmentInterface $environment)
    {
        /** @var BackendViewInterface $view */
        $view = $environment->getView();

        foreach ($view->getPanel() as $panel) {
            /** @var PanelInterface $panel */
            $sort = $panel->getElement('sort');
            if ($sort) {
                /** @var SortElementInterface $sort */
                return $sort->getSelectedDefinition();
            }
        }

        /** @var Contao2BackendViewDefinitionInterface $viewSection */
        $viewSection = $environment->getDataDefinition()->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $definition  = $viewSection->getListingConfig()->getGroupAndSortingDefinition();
        if ($definition->hasDefault()) {
            return $definition->getDefault();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public static function getManualSortingProperty(EnvironmentInterface $environment)
    {
        /** @var BackendViewInterface $view */
        $view = $environment->getView();

        $definition = null;
        foreach ($view->getPanel() as $panel) {
            /** @var PanelInterface $panel */
            $sort = $panel->getElement('sort');
            if ($sort) {
                /** @var SortElementInterface $sort */
                $definition = $sort->getSelectedDefinition();
            }
        }

        if ($definition === null) {
            /** @var Contao2BackendViewDefinitionInterface $viewDefinition */
            $dataDefinition            = $environment->getDataDefinition();
            $viewDefinition            = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
            $groupAndSortingDefinition = $viewDefinition->getListingConfig()->getGroupAndSortingDefinition();

            if ($groupAndSortingDefinition->hasDefault()) {
                $definition = $groupAndSortingDefinition->getDefault();
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
        $dataConfig->setSorting(array());
        $panel->initialize($dataConfig);

        // Restore default sorting if panel did not set any.
        if ($sorting && !$dataConfig->getSorting()) {
            $dataConfig->setSorting($sorting);
        }

        // Initialize sorting if not present yet.
        if (!$dataConfig->getSorting() && $listingConfig->getGroupAndSortingDefinition()->hasDefault()) {
            $newSorting = array();
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
        if ((!$sorting)
            || (!$sorting->getCount())
            || $sorting->get(0)->getSortingMode() === GroupAndSortingInformationInterface::SORT_RANDOM
        ) {
            return null;
        }
        $firstSorting = $sorting->get(0);

        // Use the information from the property, if given.
        if ($firstSorting->getGroupingMode() != '') {
            $groupMode   = $firstSorting->getGroupingMode();
            $groupLength = $firstSorting->getGroupingLength();
        } else {
            // No sorting? No grouping!
            $groupMode   = GroupAndSortingInformationInterface::GROUP_NONE;
            $groupLength = 0;
        }

        return array(
            'mode'     => $groupMode,
            'length'   => $groupLength,
            'property' => $firstSorting->getProperty(),
            'sorting'  => $sorting
        );
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

        $environment->getEventDispatcher()->dispatch($event::NAME, $event);

        if ($event->getRendered() !== null) {
            return $event->getRendered();
        }

        return $event->getValue();
    }

    /**
     * Redirects to the real back end module.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    public static function redirectHome(EnvironmentInterface $environment)
    {
        $input = $environment->getInputProvider();

        if ($input->hasParameter('table') && $input->hasParameter('pid')) {
            if ($input->hasParameter('pid')) {
                $event = new RedirectEvent(
                    sprintf(
                        'contao/main.php?do=%s&table=%s&pid=%s',
                        $input->getParameter('do'),
                        $input->getParameter('table'),
                        $input->getParameter('pid')
                    )
                );
            } else {
                $event = new RedirectEvent(
                    sprintf(
                        'contao/main.php?do=%s&table=%s',
                        $input->getParameter('do'),
                        $input->getParameter('table')
                    )
                );
            }
        } else {
            $event = new RedirectEvent(
                sprintf(
                    'contao/main.php?do=%s',
                    $input->getParameter('do')
                )
            );
        }

        $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_REDIRECT, $event);
    }
}
