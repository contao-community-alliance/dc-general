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
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Subscriber;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
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
        if (!$this->getScopeDeterminator()->currentScopeIsBackend()) {
            return;
        }

        $environment = $event->getEnvironment();
        $model       = $event->getModel();

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        /** @var Contao2BackendViewDefinitionInterface $viewSection */
        $viewSection   = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $listing       = $viewSection->getListingConfig();
        $properties    = $dataDefinition->getPropertiesDefinition();
        $formatter     = $listing->getLabelFormatter($model->getProviderName());
        $sorting       = ViewHelpers::getGroupingMode($environment);
        $firstSorting  = $this->getFirstSorting(($sorting['sorting'] ?? null));
        $propertyNames = $formatter->getPropertyNames();

        $modelToLabelEvent = new ModelToLabelEvent($environment, $model);
        $modelToLabelEvent
            ->setArgs($this->prepareLabelArguments($propertyNames, $properties, $environment, $model))
            ->setLabel($formatter->getFormat())
            ->setFormatter($formatter);

        if (null === ($dispatcher = $environment->getEventDispatcher())) {
            return;
        }

        $dispatcher->dispatch($modelToLabelEvent, ModelToLabelEvent::NAME);

        // Add columns.
        if ($listing->getShowColumns()) {
            $event->setLabel($this->renderWithColumns($propertyNames, $modelToLabelEvent->getArgs(), $firstSorting));
            return;
        }

        $event->setLabel(
            [
                [
                    'colspan' => null,
                    'class'   => 'tl_file_list',
                    'content' => $this->renderSingleValue(
                        $modelToLabelEvent->getLabel(),
                        $modelToLabelEvent->getArgs(),
                        $formatter->getMaxLength()
                    )
                ]
            ]
        );
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
     * @return array<string, string>
     */
    private function prepareLabelArguments(
        $propertyNames,
        PropertiesDefinitionInterface $properties,
        EnvironmentInterface $environment,
        ModelInterface $model
    ) {
        $args = [];
        foreach ($propertyNames as $propertyName) {
            if (!$properties->hasProperty($propertyName)) {
                $args[$propertyName] = '-';

                continue;
            }

            $args[$propertyName] = (string) ViewHelpers::getReadableFieldValue(
                $environment,
                $properties->getProperty($propertyName),
                $model
            );
        }

        return $args;
    }

    /**
     * Render for column layout.
     *
     * @param string[]                     $propertyNames The properties.
     * @param string|array<string, string> $args          The rendered arguments.
     * @param string                       $firstSorting  The sorting column.
     *
     * @return array
     */
    private function renderWithColumns(array $propertyNames, array|string $args, string $firstSorting)
    {
        $label = [];
        if (!\is_array($args)) {
            // @codingStandardsIgnoreStart
            @\trigger_error('Warning, column layout without arguments will not be supported.', E_USER_DEPRECATED);
            // @codingStandardsIgnoreEnd
            $label[] = [
                'colspan' => \count($propertyNames),
                'class'   => 'tl_file_list col_all',
                'content' => $args
            ];
        } else {
            foreach ($propertyNames as $propertyName) {
                $class = 'tl_file_list col_' . $propertyName;
                if ($firstSorting === $propertyName) {
                    $class .= ' ordered_by';
                }

                $label[] = [
                    'colspan' => 1,
                    'class'   => $class,
                    'content' => ($args[$propertyName] ?? '') ?: '-'
                ];
            }
        }

        return $label;
    }

    /**
     * Render as single value.
     *
     * @param string                       $label     The label string.
     * @param string|array<string, string> $args      The rendered arguments.
     * @param null|int                     $maxLength The maximum length for the label or null to allow unlimited.
     *
     * @return string
     */
    private function renderSingleValue(string $label, array|string $args, ?int $maxLength = null)
    {
        // BC: sometimes the label was returned as string in the arguments instead of an array.
        $string = !\is_array($args) ? $args : \vsprintf($label, $args);

        if ((null !== $maxLength) && \strlen($string) > $maxLength) {
            $string = \substr($string, 0, $maxLength);
        }

        return $string;
    }
}
