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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\ArrayUtil;
use Contao\Config;
use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistryInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BackendViewInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class handles the rendering of parented list view "showAll" actions.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ParentedListViewShowAllHandler extends AbstractListShowAllHandler
{
    /**
     * {@inheritDoc}
     */
    protected function wantToHandle($mode, Action $action)
    {
        return BasicDefinitionInterface::MODE_PARENTEDLIST === $mode;
    }

    /**
     * {@inheritDoc}
     *
     * Render a model - this allows to override the rendering via parent-child-record-event.
     */
    protected function renderModel(ModelInterface $model, EnvironmentInterface $environment)
    {
        $event = new ParentViewChildRecordEvent($environment, $model);

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($event, $event::NAME);

        $information = [
            [
                'colspan' => 1,
                'class'   => 'tl_file_list col_1',
                'content' => $event->getHtml()
            ]
        ];
        $model->setMeta($model::LABEL_VALUE, $information);

        parent::renderModel($model, $environment);
    }

    /**
     * {@inheritDoc}
     */
    protected function determineTemplate($groupingInformation)
    {
        // Add template.
        if (
            isset($groupingInformation['mode'])
            && (GroupAndSortingInformationInterface::GROUP_NONE !== $groupingInformation['mode'])
        ) {
            return $this->getTemplate('dcbe_general_grouping');
        }
        return $this->getTemplate('dcbe_general_parentView');
    }

    /**
     * {@inheritDoc}
     */
    protected function renderTemplate(ContaoBackendViewTemplate $template, EnvironmentInterface $environment)
    {
        parent::renderTemplate($template, $environment);

        $parentModel = $this->loadParentModel($environment);
        $template
            ->set('header', $this->renderHeaderFields($parentModel, $environment))
            ->set('headerButtons', $this->getParentModelButtons($parentModel, $environment));
    }

    /**
     * Load the parent model for the current list.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralRuntimeException If the parent view requirements are not fulfilled - either no data provider
     *                                   defined or no parent model id given.
     */
    protected function loadParentModel(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $pidDetails = ModelId::fromSerialized($inputProvider->getParameter('pid'));

        if (!($provider = $environment->getDataProvider($pidDetails->getDataProviderName()))) {
            throw new DcGeneralRuntimeException(
                'ParentView needs a proper parent data provider defined, somehow none is defined?',
                1
            );
        }

        $parent = $provider->fetch($provider->getEmptyConfig()->setId($pidDetails->getId()));
        if (!$parent) {
            // No parent item found, might have been deleted.
            // We transparently create it for our filter to be able to filter to nothing.
            $parent = $provider->getEmptyModel();
            $parent->setID($pidDetails->getId());
        }

        return $parent;
    }

    /**
     * Render the header of the parent view with information from the parent table.
     *
     * @param ModelInterface       $parentModel The parent model.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array
     */
    private function renderHeaderFields($parentModel, EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $parentName = $definition->getBasicDefinition()->getParentDataProvider();
        assert(\is_string($parentName));

        $add = [];

        $parentDefinition = $environment->getParentDataDefinition();
        assert($parentDefinition instanceof ContainerInterface);

        $properties = $parentDefinition->getPropertiesDefinition();
        foreach ($this->getViewSection($definition)->getListingConfig()->getHeaderPropertyNames() as $field) {
            $value = StringUtil::deserialize($parentModel->getProperty($field));

            if ('tstamp' === $field) {
                $value = date(Config::get('datimFormat'), $value);
            } else {
                $value = $this->renderParentProperty($environment, $properties->getProperty($field), $value);
            }

            // Add the field.
            if ('' !== $value) {
                $add[$this->translateHeaderColumnName($field, $parentName)] = $value;
            }
        }

        $event = new GetParentHeaderEvent($environment, $parentModel);
        $event->setAdditional($add);

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($event, GetParentHeaderEvent::NAME);

        if ($event->getAdditional()) {
            $add = $event->getAdditional();
        }

        return \array_map(
            function ($value) {
                if (\is_array($value)) {
                    return $value[0];
                }
                return $value;
            },
            $add
        );
    }

    /**
     * Translate a column name for use in parent model display section.
     *
     * @param string $field      The field name.
     * @param string $parentName The parent definition name.
     *
     * @return string
     */
    private function translateHeaderColumnName($field, $parentName)
    {
        // New way via symfony translator.
        if ($field . '.label' !== ($header = $this->translate($field . '.label', $parentName))) {
            return $header;
        }
        // FIXME: deprecation here? - old translation handling.

        return ('tstamp' === $field)
            ? $this->translate('tstamp', 'contao_dc-general')
            : $this->translate(\sprintf('%s.0', $field), 'contao_' . $parentName);
    }

    /**
     * Render a property of the parent model.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param PropertyInterface    $property    The property.
     * @param mixed                $value       The value to format.
     *
     * @return string
     */
    private function renderParentProperty(EnvironmentInterface $environment, $property, $value)
    {
        /** @var array{reference?: array, isAssociative?: bool} $evaluation */
        $evaluation = $property->getExtra();

        if (\is_array($value)) {
            return \implode(', ', $value);
        }

        $isRendered = false;

        $value = $this->renderForCheckbox($property, $value, $isRendered);
        $value = $this->renderForDateTime($environment, $property, $value, $isRendered);
        $value = isset($evaluation['reference'])
            ? $this->renderReference($value, $evaluation['reference'], $isRendered)
            : $value;

        $options = $property->getOptions();
        if (\is_array($options) && (($evaluation['isAssociative'] ?? false) || ArrayUtil::isAssoc($options))) {
            $value = $options[$value];
        }

        return $value ?? '';
    }

    /**
     * Render for checkbox.
     *
     * @param PropertyInterface $property   The property.
     * @param mixed             $value      The value.
     * @param boolean           $isRendered Determine if is rendered.
     *
     * @return mixed
     */
    private function renderForCheckbox(PropertyInterface $property, $value, &$isRendered)
    {
        $evaluation = $property->getExtra();

        if (
            (true === $isRendered)
            || (isset($evaluation['multiple']) && $evaluation['multiple'])
            || !('checkbox' === $property->getWidgetType())
        ) {
            return $value;
        }

        $isRendered = true;

        return !empty($value)
            ? $this->translate('yes', 'contao_dc-general')
            : $this->translate('no', 'contao_dc-general');
    }

    /**
     * Render for date time.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param PropertyInterface    $property    The property.
     * @param mixed                $value       The value.
     * @param boolean              $isRendered  Determine if is rendered.
     *
     * @return mixed
     */
    private function renderForDateTime(
        EnvironmentInterface $environment,
        PropertyInterface $property,
        $value,
        &$isRendered
    ) {
        $evaluation = $property->getExtra();

        if (
            (true === $isRendered)
            || !$value
            || !isset($evaluation['rgxp'])
            || !\in_array($evaluation['rgxp'], ['date', 'time', 'datim'])
        ) {
            return $value;
        }

        $isRendered = true;

        $event = new ParseDateEvent($value, Config::get($evaluation['rgxp'] . 'Format'));

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($event, ContaoEvents::DATE_PARSE);
        return $event->getResult();
    }

    /**
     * Render a referenced value.
     *
     * @param mixed   $value      The value to render.
     * @param array   $reference  The reference array.
     * @param boolean $isRendered Determine if is rendered.
     *
     * @return mixed
     */
    private function renderReference($value, $reference, &$isRendered)
    {
        if ((true === $isRendered) || !isset($reference[$value])) {
            return $value;
        }

        $isRendered = true;

        if (\is_array($reference[$value])) {
            return $reference[$value][0];
        }

        return $reference[$value];
    }

    /**
     * Retrieve a list of html buttons to use in the top panel (submit area).
     *
     * @param ModelInterface       $parentModel The parent model.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    private function getParentModelButtons($parentModel, EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if ('select' === $inputProvider->getParameter('act')) {
            return '';
        }

        $registry = $environment->getBaseConfigRegistry();
        assert($registry instanceof BaseConfigRegistryInterface);

        $view = $environment->getView();
        assert($view instanceof BackendViewInterface);

        $panel = $view->getPanel();
        assert($panel instanceof PanelContainerInterface);

        $config = $registry->getBaseConfig();

        $panel->initialize($config);
        if (!$config->getSorting()) {
            return '';
        }

        return \implode(
            ' ',
            [
                'editHeader' => $this->getHeaderEditButton($parentModel, $environment),
                'pasteNew'   => $this->getHeaderPasteNewButton($parentModel, $environment),
                'pasteAfter' => $this->getHeaderPasteTopButton($parentModel, $environment)
            ]
        );
    }

    /**
     * Retrieve a list of html buttons to use in the top panel (submit area).
     *
     * @param ModelInterface       $parentModel The parent model.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return null|string
     */
    protected function getHeaderEditButton(ModelInterface $parentModel, EnvironmentInterface $environment)
    {
        $parentDefinition = $environment->getParentDataDefinition();
        assert($parentDefinition instanceof ContainerInterface);

        $backendView = $parentDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        assert($backendView instanceof Contao2BackendViewDefinitionInterface);

        /** @var CommandCollectionInterface $commands */
        $commands = $backendView->getModelCommands();

        if (!$commands->hasCommandNamed('edit') || !$parentDefinition->getBasicDefinition()->isEditable()) {
            return null;
        }

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $parentName = $definition->getBasicDefinition()->getParentDataProvider();
        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $command    = $commands->getCommandNamed('edit');
        $parameters = (array) $command->getParameters();

        // This should be set in command builder rather than here.
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $parameters['do']    = $inputProvider->getParameter('do');
        $parameters['table'] = $parentName;
        $parameters['pid']   = '';

        /** @var array{idparam?: string} $extra */
        $extra = (array) $command->getExtra();
        if (null !== ($idParam = ($extra['idparam'] ?? null))) {
            $parameters[$idParam] = ModelId::fromModel($parentModel)->getSerialized();
        } else {
            $parameters['id'] = ModelId::fromModel($parentModel)->getSerialized();
        }

        if (null !== ($pid = $this->getGrandParentId($parentDefinition, $parentModel, $environment))) {
            if (false === $pid) {
                return null;
            }
            $parameters['pid'] = $pid;
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            new GenerateHtmlEvent(
                'edit.svg',
                $this->translateButtonLabel('editheader', $parentDefinition->getName())
            ),
            ContaoEvents::IMAGE_GET_HTML
        );

        $href = '';
        foreach ($parameters as $key => $value) {
            $href .= \sprintf('&%s=%s', $key, $value ?? '');
        }
        /** @var AddToUrlEvent $urlAfter */
        $urlAfter = $dispatcher->dispatch(new AddToUrlEvent($href), ContaoEvents::BACKEND_ADD_TO_URL);

        return \sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $urlAfter->getUrl(),
            StringUtil::specialchars(
                $this->translateButtonDescription(
                    'editheader',
                    $parentDefinition->getName(),
                    ['%id%' => $parentModel->getId()]
                )
            ),
            $imageEvent->getHtml() ?? ''
        );
    }

    /**
     * Retrieve the header button for paste new.
     *
     * @param ModelInterface       $parentModel The parent model.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return null|string
     */
    private function getHeaderPasteNewButton(ModelInterface $parentModel, EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();
        assert($basicDefinition instanceof BasicDefinitionInterface);

        if (!$basicDefinition->isCreatable()) {
            return null;
        }

        $dataProvider = $basicDefinition->getDataProvider();
        assert(\is_string($dataProvider));

        $filter = new Filter();
        $filter->andModelIsFromProvider($dataProvider);
        if (null !== ($parentProviderName = $basicDefinition->getParentDataProvider())) {
            $filter->andParentIsFromProvider($parentProviderName);
        } else {
            $filter->andHasNoParent();
        }

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        if ($clipboard->isNotEmpty($filter)) {
            return null;
        }

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $dispatcher->dispatch(
            new AddToUrlEvent('act=create&amp;pid=' . ModelId::fromModel($parentModel)->getSerialized()),
            ContaoEvents::BACKEND_ADD_TO_URL
        );

        $parentDefinition = $environment->getParentDataDefinition();
        assert($parentDefinition instanceof ContainerInterface);

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            new GenerateHtmlEvent(
                'new.svg',
                $this->translateButtonLabel('pastenew', $parentDefinition->getName())
            ),
            ContaoEvents::IMAGE_GET_HTML
        );

        return \sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $urlEvent->getUrl(),
            StringUtil::specialchars($this->translateButtonLabel('pastenew', $parentDefinition->getName())),
            $imageEvent->getHtml() ?? ''
        );
    }

    /**
     * Retrieve the header button for paste new.
     *
     * @param ModelInterface       $parentModel The parent model.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return null|string
     */
    private function getHeaderPasteTopButton(ModelInterface $parentModel, EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();
        assert($basicDefinition instanceof BasicDefinitionInterface);

        $dataProvider = $basicDefinition->getDataProvider();
        assert(\is_string($dataProvider));

        $dataParentProvider = $basicDefinition->getParentDataProvider();
        assert(\is_string($dataParentProvider));

        $filter = new Filter();
        $filter->andModelIsFromProvider($dataProvider);
        $filter->andParentIsFromProvider($dataParentProvider);

        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        if ($clipboard->isEmpty($filter)) {
            return null;
        }

        if ($allowPasteTop = (bool) ViewHelpers::getManualSortingProperty($environment)) {
            $subFilter = new Filter();
            $subFilter->andActionIsNotIn([ItemInterface::COPY, ItemInterface::DEEP_COPY]);
            $subFilter->andParentIsNot(ModelId::fromModel($parentModel));
            $subFilter->orActionIsIn([ItemInterface::COPY, ItemInterface::DEEP_COPY]);

            $dataProvider = $basicDefinition->getDataProvider();
            assert(\is_string($dataProvider));

            $dataParentProvider = $basicDefinition->getParentDataProvider();
            assert(is_string($dataParentProvider));

            $filter = new Filter();
            $filter->andModelIsFromProvider($dataProvider);
            $filter->andParentIsFromProvider($dataParentProvider);
            $filter->andSub($subFilter);

            $allowPasteTop = (bool) $clipboard->fetch($filter);
        }

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dataProvider = $basicDefinition->getDataProvider();
        assert(\is_string($dataProvider));

        if ($allowPasteTop) {
            /** @var AddToUrlEvent $urlEvent */
            $urlEvent = $dispatcher->dispatch(
                new AddToUrlEvent(
                    'act=paste' .
                    '&amp;pid=' . ModelId::fromModel($parentModel)->getSerialized() .
                    '&amp;after=' . ModelId::fromValues($dataProvider, '0')->getSerialized()
                ),
                ContaoEvents::BACKEND_ADD_TO_URL
            );

            /** @var GenerateHtmlEvent $imageEvent */
            $imageEvent = $dispatcher->dispatch(
                new GenerateHtmlEvent(
                    'pasteafter.svg',
                    $this->translateButtonLabel('pasteafter', $definition->getName()),
                    'class="blink"'
                ),
                ContaoEvents::IMAGE_GET_HTML
            );

            return \sprintf(
                '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
                $urlEvent->getUrl(),
                StringUtil::specialchars($this->translateButtonLabel('pasteafter', $definition->getName())),
                $imageEvent->getHtml() ?? ''
            );
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            new GenerateHtmlEvent(
                'pasteafter_.svg',
                $this->translateButtonLabel('pasteafter', $definition->getName()),
                'class="blink"'
            ),
            ContaoEvents::IMAGE_GET_HTML
        );

        return $imageEvent->getHtml();
    }

    /**
     * Obtain the id of the grand-parent (if any).
     *
     * @param ContainerInterface   $parentDefinition The parent definition.
     * @param ModelInterface       $parentModel      The parent model.
     * @param EnvironmentInterface $environment      The environment.
     *
     * @return false|null|string
     */
    private function getGrandParentId(
        ContainerInterface $parentDefinition,
        ModelInterface $parentModel,
        EnvironmentInterface $environment
    ) {
        if (null === ($grandParentName = $parentDefinition->getBasicDefinition()->getParentDataProvider())) {
            return null;
        }

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $relationship = $definition->getModelRelationshipDefinition()->getChildCondition(
            $grandParentName,
            $parentDefinition->getName()
        );

        if (!$relationship) {
            return null;
        }

        $grandParentProvider = $environment->getDataProvider($grandParentName);
        assert($grandParentProvider instanceof DataProviderInterface);

        $config = $grandParentProvider->getEmptyConfig();
        $config->setFilter((array) $relationship->getInverseFilterFor($parentModel));

        $parents = $grandParentProvider->fetchAll($config);
        assert($parents instanceof CollectionInterface);

        $firstModel = $parents->get(0);
        assert($firstModel instanceof ModelInterface);

        if (1 === $parents->length()) {
            return ModelId::fromModel($firstModel)->getSerialized();
        }

        return false;
    }
}
