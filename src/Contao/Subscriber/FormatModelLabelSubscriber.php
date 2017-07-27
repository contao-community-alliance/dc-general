<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Subscriber;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;

/**
 * This class is the base foundation for a command event.
 */
class FormatModelLabelSubscriber
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * Default handler for formatting a model.
     *
     * @param FormatModelLabelEvent $event The event.
     *
     * @return void
     */
    public function handleFormatModelLabel(FormatModelLabelEvent $event)
    {
        $environment = $event->getEnvironment();
        $model       = $event->getModel();

        $dataDefinition = $environment->getDataDefinition();
        /** @var Contao2BackendViewDefinitionInterface $viewSection */
        $viewSection       = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $listing           = $viewSection->getListingConfig();
        $properties        = $dataDefinition->getPropertiesDefinition();
        $formatter         = $listing->getLabelFormatter($model->getProviderName());
        $sorting           = ViewHelpers::getGroupingMode($environment);
        $sortingDefinition = $sorting['sorting'];
        $firstSorting      = $this->getFirstSorting($sortingDefinition);
        $propertyNames     = $formatter->getPropertyNames();

        $args = $this->prepareLabelArguments($propertyNames, $properties, $environment, $model);

        $modelToLabelEvent = new ModelToLabelEvent($environment, $model);
        $modelToLabelEvent
            ->setArgs($args)
            ->setLabel($formatter->getFormat())
            ->setFormatter($formatter);

        $environment->getEventDispatcher()->dispatch(ModelToLabelEvent::NAME, $modelToLabelEvent);

        // Add columns.
        if ($listing->getShowColumns()) {
            $event->setLabel($this->renderWithColumns($propertyNames, $modelToLabelEvent->getArgs(), $firstSorting));
            return;
        }

        $event->setLabel([[
            'colspan' => null,
            'class'   => 'tl_file_list',
            'content' => $this->renderSingleValue(
                $modelToLabelEvent->getLabel(),
                $modelToLabelEvent->getArgs(),
                $formatter->getMaxLength()
            )
        ]]);
    }

    /**
     * Retrieve the first sorting value.
     *
     * @param GroupAndSortingDefinitionInterface|null $sortingDefinition The sorting definition.
     *
     * @return string
     */
    private function getFirstSorting(GroupAndSortingDefinitionInterface $sortingDefinition = null)
    {
        if (null === $sortingDefinition) {
            return '';
        }

        foreach ($sortingDefinition as $information) {
            /** @var GroupAndSortingInformationInterface $information */
            if ($information->getProperty()) {
                return $information->getProperty();
            }
        }

        return '';
    }

    /**
     * Prepare the arguments for the label formatter.
     *
     * @param string[]                      $propertyNames The properties.
     * @param PropertiesDefinitionInterface $properties    The property definition.
     * @param EnvironmentInterface          $environment   The environment.
     * @param ModelInterface                $model         The model.
     *
     * @return array
     */
    private function prepareLabelArguments($propertyNames, $properties, $environment, $model)
    {
        $args = [];
        foreach ($propertyNames as $propertyName) {
            if ($properties->hasProperty($propertyName)) {
                $args[$propertyName] = (string) ViewHelpers::getReadableFieldValue(
                    $environment,
                    $properties->getProperty($propertyName),
                    $model
                );
            } else {
                $args[$propertyName] = '-';
            }
        }

        return $args;
    }

    /**
     * Render for column layout.
     *
     * @param string[] $propertyNames The properties.
     * @param string[] $args          The rendered arguments.
     * @param string   $firstSorting  The sorting column.
     *
     * @return array
     */
    private function renderWithColumns($propertyNames, $args, $firstSorting)
    {
        $label = [];
        if (!is_array($args)) {
            trigger_error('Warning, column layout without arguments will not be supported.', E_USER_DEPRECATED);
            $label[] = [
                'colspan' => count($propertyNames),
                'class'   => 'tl_file_list col_all',
                'content' => $args
            ];
        } else {
            foreach ($propertyNames as $propertyName) {
                $class = 'tl_file_list col_' . $propertyName;
                if ($propertyName == $firstSorting) {
                    $class .= ' ordered_by';
                }

                $label[] = [
                    'colspan' => 1,
                    'class'   => $class,
                    'content' => $args[$propertyName] ?: '-'
                ];
            }
        }

        return $label;
    }

    /**
     * Render as single value.
     *
     * @param string   $label     The label string.
     * @param string[] $args      The rendered arguments.
     * @param null     $maxLength The maximum length for the label or null to allow unlimited.
     *
     * @return string
     */
    private function renderSingleValue($label, $args, $maxLength = null)
    {
        // BC: sometimes the label was returned as string in the arguments instead of an array.
        if (!is_array($args)) {
            $string = $args;
        } else {
            $string = vsprintf($label, $args);
        }

        if ($maxLength !== null && strlen($string) > $maxLength) {
            $string = substr($string, 0, $maxLength);
        }

        return $string;
    }
}
