<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Subscriber;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent;

/**
 * This class is the base foundation for a command event.
 */
class FormatModelLabelSubscriber
{
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
        $firstSorting      = '';

        if ($sortingDefinition) {
            /** @var GroupAndSortingDefinitionInterface $sortingDefinition */
            foreach ($sortingDefinition as $information) {
                /** @var GroupAndSortingInformationInterface $information */
                if ($information->getProperty()) {
                    $firstSorting = reset($sorting);
                    break;
                }
            }
        }

        $args = array();
        foreach ($formatter->getPropertyNames() as $propertyName) {
            if ($properties->hasProperty($propertyName)) {
                $property = $properties->getProperty($propertyName);

                $args[$propertyName] = (string) ViewHelpers::getReadableFieldValue(
                    $environment,
                    $property,
                    $model
                );
            } else {
                $args[$propertyName] = '-';
            }
        }

        $modelToLabelEvent = new ModelToLabelEvent($environment, $model);
        $modelToLabelEvent
            ->setArgs($args)
            ->setLabel($formatter->getFormat())
            ->setFormatter($formatter);

        $environment->getEventDispatcher()->dispatch(ModelToLabelEvent::NAME, $modelToLabelEvent);

        $label = array();

        // Add columns.
        if ($listing->getShowColumns()) {
            $fields = $formatter->getPropertyNames();
            $args   = $modelToLabelEvent->getArgs();

            if (!is_array($args)) {
                $label[] = array(
                    'colspan' => count($fields),
                    'class'   => 'tl_file_list col_1',
                    'content' => $args
                );
            } else {
                foreach ($fields as $j => $propertyName) {
                    $label[] = array(
                        'colspan' => 1,
                        'class'   => 'tl_file_list col_' . $j . (($propertyName == $firstSorting) ? ' ordered_by' : ''),
                        'content' => (($args[$propertyName] != '') ? $args[$propertyName] : '-')
                    );
                }
            }
        } else {
            if (!is_array($modelToLabelEvent->getArgs())) {
                $string = $modelToLabelEvent->getArgs();
            } else {
                $string = vsprintf($modelToLabelEvent->getLabel(), $modelToLabelEvent->getArgs());
            }

            if ($formatter->getMaxLength() !== null && strlen($string) > $formatter->getMaxLength()) {
                $string = substr($string, 0, $formatter->getMaxLength());
            }

            $label[] = array(
                'colspan' => null,
                'class'   => 'tl_file_list',
                'content' => $string
            );
        }

        $event->setLabel($label);
    }
}
