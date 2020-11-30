<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber;

use Contao\Config;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Patchwork\Utf8;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles the group header formatting.
 */
class GetGroupHeaderSubscriber
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     * @param TranslatorInterface      $translator The translator.
     */
    public function __construct(EventDispatcherInterface $dispatcher, TranslatorInterface $translator)
    {
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
    }

    /**
     * Handle the subscribed event.
     *
     * @param GetGroupHeaderEvent $event The event.
     *
     * @return void
     */
    public function handle(GetGroupHeaderEvent $event)
    {
        if ((null !== $event->getValue()) || !$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $environment = $event->getEnvironment();
        $property    = $environment
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($event->getGroupField());

        // No property? Get out!
        if (!$property) {
            $event->setValue('-');
            return;
        }

        $value = $this->formatGroupHeader(
            $environment,
            $event->getModel(),
            $property,
            $event->getGroupingMode(),
            $event->getGroupingLength()
        );

        if (null !== $value) {
            $event->setValue($value);
        }
    }

    /**
     * Get the group header.
     *
     * @param EnvironmentInterface $environment    The environment.
     * @param ModelInterface       $model          The model.
     * @param PropertyInterface    $property       The property.
     * @param int                  $groupingMode   The grouping mode.
     * @param int                  $groupingLength The grouping length.
     *
     * @return string
     */
    protected function formatGroupHeader(
        EnvironmentInterface $environment,
        ModelInterface $model,
        PropertyInterface $property,
        $groupingMode,
        $groupingLength
    ) {
        $evaluation = $property->getExtra();

        if (!$evaluation['multiple'] && ('checkbox' === $property->getWidgetType())) {
            return $this->formatCheckboxOptionLabel($model->getProperty($property->getName()));
        }
        if (GroupAndSortingInformationInterface::GROUP_NONE !== $groupingMode) {
            return $this->formatByGroupingMode($groupingMode, $groupingLength, $environment, $property, $model);
        }

        $value = ViewHelpers::getReadableFieldValue($environment, $property, $model);

        if (isset($evaluation['reference'])) {
            $remoteNew = $evaluation['reference'][$value];
        } elseif (\array_is_assoc($property->getOptions())) {
            $options   = $property->getOptions();
            $remoteNew = $options[$value];
        } else {
            $remoteNew = $value;
        }

        if (\is_array($remoteNew)) {
            $remoteNew = $remoteNew[0];
        }

        if (empty($remoteNew)) {
            $remoteNew = '-';
        }

        return $remoteNew;
    }

    /**
     * Format the grouping header for a checkbox option.
     *
     * @param string $value The given value.
     *
     * @return string
     */
    private function formatCheckboxOptionLabel($value)
    {
        return ('' !== $value)
            ? \ucfirst($this->translator->translate('MSC.yes'))
            : \ucfirst($this->translator->translate('MSC.no'));
    }

    /**
     * Format the group header by the grouping mode.
     *
     * @param int                  $groupingMode   The grouping mode.
     * @param int                  $groupingLength The grouping length.
     * @param EnvironmentInterface $environment    The environment.
     * @param PropertyInterface    $property       The current property definition.
     * @param ModelInterface       $model          The current data model.
     *
     * @return string
     */
    private function formatByGroupingMode(
        $groupingMode,
        $groupingLength,
        EnvironmentInterface $environment,
        PropertyInterface $property,
        ModelInterface $model
    ) {
        switch ($groupingMode) {
            case GroupAndSortingInformationInterface::GROUP_CHAR:
                return $this->formatByCharGrouping(
                    ViewHelpers::getReadableFieldValue($environment, $property, $model),
                    $groupingLength
                );

            case GroupAndSortingInformationInterface::GROUP_DAY:
                return $this->formatByDayGrouping((int) $model->getProperty($property->getName()));

            case GroupAndSortingInformationInterface::GROUP_WEEK:
                return $this->formatByWeekGrouping((int) $model->getProperty($property->getName()));

            case GroupAndSortingInformationInterface::GROUP_MONTH:
                return $this->formatByMonthGrouping((int) $model->getProperty($property->getName()));

            case GroupAndSortingInformationInterface::GROUP_YEAR:
                return $this->formatByYearGrouping((int) $model->getProperty($property->getName()));

            default:
                return ViewHelpers::getReadableFieldValue($environment, $property, $model);
        }
    }

    /**
     * Format a value for char grouping.
     *
     * @param string $value          The value.
     * @param int    $groupingLength The group length.
     *
     * @return string
     */
    private function formatByCharGrouping($value, $groupingLength)
    {
        if ('' === $value) {
            return '-';
        }

        return \ucfirst(Utf8::substr($value, 0, $groupingLength ?: null));
    }

    /**
     * Render a grouping header for day.
     *
     * @param int $value The value.
     *
     * @return string
     */
    private function formatByDayGrouping($value)
    {
        $value = $this->getTimestamp($value);
        if ('' === $value) {
            return '-';
        }
        $event = new ParseDateEvent($value, Config::get('dateFormat'));
        $this->dispatcher->dispatch(ContaoEvents::DATE_PARSE, $event);

        return $event->getResult();
    }

    /**
     * Render a grouping header for week.
     *
     * @param int $value The value.
     *
     * @return string
     */
    private function formatByWeekGrouping($value)
    {
        $value = $this->getTimestamp($value);
        if ('' === $value) {
            return '-';
        }
        $event = new ParseDateEvent($value, 'W. Y');
        $this->dispatcher->dispatch(ContaoEvents::DATE_PARSE, $event);

        return $event->getResult();
    }

    /**
     * Render a grouping header for month.
     *
     * @param int $value The value.
     *
     * @return string
     */
    private function formatByMonthGrouping($value)
    {
        $value = $this->getTimestamp($value);
        if ('' === $value) {
            return '-';
        }
        $event = new ParseDateEvent($value, 'F Y');
        $this->dispatcher->dispatch(ContaoEvents::DATE_PARSE, $event);

        return $event->getResult();
    }

    /**
     * Render a grouping header for year.
     *
     * @param int $value The value.
     *
     * @return string
     */
    private function formatByYearGrouping($value)
    {
        $value = $this->getTimestamp($value);
        if ('' === $value) {
            return '-';
        }

        return date('Y', $value);
    }

    /**
     * Make sure a timestamp is returned.
     *
     * @param int|\DateTime $value The given date.
     *
     * @return int
     */
    private function getTimestamp($value)
    {
        return ($value instanceof \DateTime) ? $value->getTimestamp() : $value;
    }
}
