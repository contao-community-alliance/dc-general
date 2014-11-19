<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\SortElementInterface;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ViewHelpers
{
    /**
     * Retrieve the currently active sorting.
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
     * Retrieve the currently active grouping mode.
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

        return array
        (
            'mode'     => $groupMode,
            'length'   => $groupLength,
            'property' => $firstSorting->getProperty(),
            'sorting'  => $sorting
        );
    }

    /**
     * Get for a field the readable value.
     *
     * @param PropertyInterface $property The property to be rendered.
     *
     * @param ModelInterface    $model    The model from which the property value shall be retrieved from.
     *
     * @param mixed             $value    The value for the property.
     *
     * @return mixed
     */
    public static function getReadableFieldValue(
        EnvironmentInterface $environment,
        PropertyInterface $property,
        ModelInterface $model,
        $value
    ) {
        $event = new RenderReadablePropertyValueEvent($environment, $model, $property, $value);

        $dispatcher = $environment->getEventDispatcher();
        $dispatcher->dispatch(
            sprintf('%s[%s][%s]', $event::NAME, $environment->getDataDefinition()->getName(), $property->getName()),
            $event
        );
        $dispatcher->dispatch(sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()), $event);
        $dispatcher->dispatch($event::NAME, $event);

        if ($event->getRendered() !== null) {
            return $event->getRendered();
        }

        return $value;
    }
}
