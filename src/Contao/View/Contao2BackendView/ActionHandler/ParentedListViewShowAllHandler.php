<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\Config;
use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetParentHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ParentViewChildRecordEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class handles the rendering of parented list view "showAll" actions.
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
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);

        if (null !== $event->getHtml()) {
            $information = [
                [
                    'colspan' => 1,
                    'class'   => 'tl_file_list col_1',
                    'content' => $event->getHtml()
                ]
            ];
            $model->setMeta($model::LABEL_VALUE, $information);
            return;
        }

        parent::renderModel($model, $environment);
    }

    /**
     * {@inheritDoc}
     */
    protected function determineTemplate($groupingInformation)
    {
        // Add template.
        if (isset($groupingInformation['mode'])
            && ($groupingInformation['mode'] != GroupAndSortingInformationInterface::GROUP_NONE)) {
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
        $template->set('header', $this->renderHeaderFields($parentModel, $environment));
        $template->set('headerButtons', $this->getParentModelButtons($parentModel, $environment));
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
        $pidDetails = ModelId::fromSerialized($environment->getInputProvider()->getParameter('pid'));

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
        $parentName = $definition->getBasicDefinition()->getParentDataProvider();
        $add        = [];
        $properties = $environment->getParentDataDefinition()->getPropertiesDefinition();
        foreach ($this->getViewSection($definition)->getListingConfig()->getHeaderPropertyNames() as $field) {
            $value = \deserialize($parentModel->getProperty($field));

            if ($field == 'tstamp') {
                $value = date(Config::get('datimFormat'), $value);
            } else {
                $value = $this->renderParentProperty($environment, $properties->getProperty($field), $value);
            }

            // Add the field.
            if ($value != '') {
                $add[$this->translateHeaderColumnName($field, $parentName)] = $value;
            }
        }

        $event = new GetParentHeaderEvent($environment, $parentModel);
        $event->setAdditional($add);

        $environment->getEventDispatcher()->dispatch(GetParentHeaderEvent::NAME, $event);

        if (!$event->getAdditional() !== null) {
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
     * @return array|string
     */
    private function translateHeaderColumnName($field, $parentName)
    {
        if ($field === 'tstamp') {
            $lang = $this->translate('MSC.tstamp', 'contao_default');
        } else {
            $lang = $this->translate(\sprintf('%s.0', $field), $parentName);
        }

        return $lang;
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
        $evaluation = $property->getExtra();

        if (\is_array($value)) {
            return \implode(', ', $value);
        }

        $isRendered = false;

        $value = $this->renderForCheckbox($property, $value, $isRendered);
        $value = $this->renderForDateTime($environment, $property, $value, $isRendered);
        $value = $this->renderReference($value, $evaluation['reference'], $isRendered);

        $options = $property->getOptions();
        if ($evaluation['isAssociative'] || \array_is_assoc($options)) {
            $value = $options[$value];
        }

        return $value;
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

        if ((true === $isRendered) || $evaluation['multiple'] || !('checkbox' === $property->getWidgetType())) {
            return $value;
        }

        $isRendered = true;

        return !empty($value)
            ? $this->translate('MSC.yes', 'contao_default')
            : $this->translate('MSC.no', 'contao_default');
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

        if ((true === $isRendered) || !$value || !\in_array($evaluation['rgxp'], ['date', 'time', 'datim'])) {
            return $value;
        }

        $isRendered = true;

        $event = new ParseDateEvent($value, Config::get($evaluation['rgxp'] . 'Format'));
        $environment->getEventDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $event);
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
        if ('select' === $environment->getInputProvider()->getParameter('act')) {
            return '';
        }

        $config = $environment->getBaseConfigRegistry()->getBaseConfig();
        $environment->getView()->getPanel()->initialize($config);
        if (!$config->getSorting()) {
            return '';
        }

        $headerButtons = [];

        $headerButtons['editHeader'] = $this->getHeaderEditButton($parentModel, $environment);
        $headerButtons['pasteNew']   = $this->getHeaderPasteNewButton($parentModel, $environment);
        $headerButtons['pasteAfter'] = $this->getHeaderPasteTopButton($parentModel, $environment);

        return \implode(' ', $headerButtons);
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
        /** @var CommandCollectionInterface $commands */
        $commands = $parentDefinition
            ->getDefinition(Contao2BackendViewDefinitionInterface::NAME)
            ->getModelCommands();

        if (!$commands->hasCommandNamed('edit') || !$parentDefinition->getBasicDefinition()->isEditable()) {
            return null;
        }

        $definition      = $environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        $parentName      = $basicDefinition->getParentDataProvider();
        $dispatcher      = $environment->getEventDispatcher();

        $command    = $commands->getCommandNamed('edit');
        $parameters = (array) $command->getParameters();

        // This should be set in command builder rather than here.
        $parameters['do']    = $environment->getInputProvider()->getParameter('do');
        $parameters['table'] = $parentName;
        $parameters['pid']   = '';

        $extra   = (array) $command->getExtra();
        $idParam = isset($extra['idparam']) ? $extra['idparam'] : null;
        if ($idParam) {
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
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'edit.svg',
                $this->translate('editheader.0', $parentDefinition->getName())
            )
        );

        $href = '';
        foreach ($parameters as $key => $value) {
            $href .= \sprintf('&%s=%s', $key, $value);
        }
        /** @var AddToUrlEvent $urlAfter */
        $urlAfter = $dispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, new AddToUrlEvent($href));

        return \sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $urlAfter->getUrl(),
            StringUtil::specialchars(
                \sprintf($this->translate('editheader.1', $parentDefinition->getName()), $parentModel->getId())
            ),
            $imageEvent->getHtml()
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
        $definition      = $environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        if (!$basicDefinition->isCreatable()) {
            return null;
        }

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        if ($parentDataProviderName = $basicDefinition->getParentDataProvider()) {
            $filter->andParentIsFromProvider($parentDataProviderName);
        } else {
            $filter->andHasNoParent();
        }

        if ($environment->getClipboard()->isNotEmpty($filter)) {
            return null;
        }
        $dispatcher = $environment->getEventDispatcher();

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $dispatcher->dispatch(
            ContaoEvents::BACKEND_ADD_TO_URL,
            new AddToUrlEvent('act=create&amp;pid=' . ModelId::fromModel($parentModel)->getSerialized())
        );

        $parentDefinition = $environment->getParentDataDefinition();
        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'new.svg',
                $this->translate('pastenew.0', $parentDefinition->getName())
            )
        );

        return \sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $urlEvent->getUrl(),
            StringUtil::specialchars($this->translate('pastenew.0', $parentDefinition->getName())),
            $imageEvent->getHtml()
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
        $definition      = $environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        $filter->andParentIsFromProvider($basicDefinition->getParentDataProvider());

        $clipboard = $environment->getClipboard();
        if ($clipboard->isEmpty($filter)) {
            return null;
        }

        $allowPasteTop = ViewHelpers::getManualSortingProperty($environment);

        if (!$allowPasteTop) {
            $subFilter = new Filter();
            $subFilter->andActionIsNotIn([ItemInterface::COPY, ItemInterface::DEEP_COPY]);
            $subFilter->andParentIsNot(ModelId::fromModel($parentModel));
            $subFilter->orActionIsIn([ItemInterface::COPY, ItemInterface::DEEP_COPY]);

            $filter = new Filter();
            $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
            $filter->andParentIsFromProvider($basicDefinition->getParentDataProvider());
            $filter->andSub($subFilter);

            $allowPasteTop = (bool) $clipboard->fetch($filter);
        }

        $dispatcher = $environment->getEventDispatcher();
        if ($allowPasteTop) {
            /** @var AddToUrlEvent $urlEvent */
            $urlEvent = $dispatcher->dispatch(
                ContaoEvents::BACKEND_ADD_TO_URL,
                new AddToUrlEvent(
                    'act=paste' .
                    '&amp;pid=' . ModelId::fromModel($parentModel)->getSerialized() .
                    '&amp;after=' . ModelId::fromValues($basicDefinition->getDataProvider(), '0')->getSerialized()
                )
            );

            /** @var GenerateHtmlEvent $imageEvent */
            $imageEvent = $dispatcher->dispatch(
                ContaoEvents::IMAGE_GET_HTML,
                new GenerateHtmlEvent(
                    'pasteafter.svg',
                    $this->translate('pasteafter.0', $definition->getName()),
                    'class="blink"'
                )
            );

            return \sprintf(
                '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
                $urlEvent->getUrl(),
                StringUtil::specialchars($this->translate('pasteafter.0', $definition->getName())),
                $imageEvent->getHtml()
            );
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'pasteafter_.svg',
                $this->translate('pasteafter.0', $definition->getName()),
                'class="blink"'
            )
        );

        return $imageEvent->getHtml();
    }

    /**
     * Obtain the id of the grand parent (if any).
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
        if ('' == ($grandParentName = $parentDefinition->getBasicDefinition()->getParentDataProvider())) {
            return null;
        }

        $container = $environment->getDataDefinition();

        $relationship = $container->getModelRelationshipDefinition()->getChildCondition(
            $grandParentName,
            $parentDefinition->getName()
        );

        if (!$relationship) {
            return null;
        }
        $filter = $relationship->getInverseFilterFor($parentModel);

        $grandParentProvider = $environment->getDataProvider($grandParentName);

        $config = $grandParentProvider->getEmptyConfig();
        $config->setFilter($filter);

        $parents = $grandParentProvider->fetchAll($config);

        if ($parents->length() == 1) {
            return ModelId::fromModel($parents->get(0))->getSerialized();
        }
        return false;
    }
}
