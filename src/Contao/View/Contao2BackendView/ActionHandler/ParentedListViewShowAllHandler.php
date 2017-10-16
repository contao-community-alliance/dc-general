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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\Config;
use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
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
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class handles the rendering of parented list view "showAll" actions.
 */
class ParentedListViewShowAllHandler extends AbstractListShowAllHandler
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * ParentedListViewShowAllHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->setScopeDeterminator($scopeDeterminator);
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        parent::process();
    }

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle($mode)
    {
        return BasicDefinitionInterface::MODE_PARENTEDLIST === $mode;
    }

    /**
     * {@inheritDoc}
     *
     * Render a model - this allows to override the rendering via parent-child-record-event.
     */
    protected function renderModel(ModelInterface $model)
    {
        $event = new ParentViewChildRecordEvent($this->environment, $model);
        $this->environment->getEventDispatcher()->dispatch($event::NAME, $event);

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
        parent::renderModel($model);
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
    protected function renderTemplate(ContaoBackendViewTemplate $template)
    {
        parent::renderTemplate($template);
        $parentModel = $this->loadParentModel();
        $template->set('header', $this->renderHeaderFields($parentModel));
        $template->set('headerButtons', $this->getParentModelButtons($parentModel));
    }

    /**
     * Load the parent model for the current list.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralRuntimeException If the parent view requirements are not fulfilled - either no data provider
     *                                   defined or no parent model id given.
     */
    private function loadParentModel()
    {
        $pidDetails = ModelId::fromSerialized($this->environment->getInputProvider()->getParameter('pid'));

        if (!($provider = $this->environment->getDataProvider($pidDetails->getDataProviderName()))) {
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
     * @param ModelInterface $parentModel The parent model.
     *
     * @return array
     */
    private function renderHeaderFields($parentModel)
    {
        $definition = $this->environment->getDataDefinition();
        $parentName = $definition->getBasicDefinition()->getParentDataProvider();
        $add        = [];
        $properties = $this->environment->getParentDataDefinition()->getPropertiesDefinition();
        foreach ($this->getViewSection()->getListingConfig()->getHeaderPropertyNames() as $field) {
            $value = deserialize($parentModel->getProperty($field));

            if ($field == 'tstamp') {
                $value = date(Config::get('datimFormat'), $value);
            } else {
                $value = $this->renderParentProperty($properties->getProperty($field), $value);
            }

            // Add the field.
            if ($value != '') {
                $add[$this->translateHeaderColumnName($field, $parentName)] = $value;
            }
        }

        $event = new GetParentHeaderEvent($this->environment, $parentModel);
        $event->setAdditional($add);

        $this->environment->getEventDispatcher()->dispatch(GetParentHeaderEvent::NAME, $event);

        if (!$event->getAdditional() !== null) {
            $add = $event->getAdditional();
        }

        return array_map(function ($value) {
            if (is_array($value)) {
                return $value[0];
            }
            return $value;
        }, $add);
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
            $lang = $this->translate('MSC.tstamp');
        } else {
            $lang = $this->translate(sprintf('%s.0', $field), $parentName);
        }

        return $lang;
    }

    /**
     * Render a property of the parent model.
     *
     * @param PropertyInterface $property The property.
     * @param mixed             $value    The value to format.
     *
     * @return string
     */
    private function renderParentProperty($property, $value)
    {
        $evaluation = $property->getExtra();

        if (is_array($value)) {
            return implode(', ', $value);
        }

        if ($property->getWidgetType() == 'checkbox' && !$evaluation['multiple']) {
            return !empty($value)
                ? $this->translate('MSC.yes')
                : $this->translate('MSC.no');
        }
        if ($value && in_array($evaluation['rgxp'], ['date', 'time', 'datim'])) {
            $event = new ParseDateEvent($value, Config::get($evaluation['rgxp'] . 'Format'));
            $this->environment->getEventDispatcher()->dispatch(ContaoEvents::DATE_PARSE, $event);
            return $event->getResult();
        }
        if (isset($evaluation['reference'][$value])) {
            return $this->renderReference($value, $evaluation['reference']);
        }
        $options = $property->getOptions();
        if ($evaluation['isAssociative'] || array_is_assoc($options)) {
            $value = $options[$value];
        }

        return $value;
    }

    /**
     * Render a referenced value.
     *
     * @param mixed $value     The value to render.
     * @param array $reference The reference array.
     *
     * @return mixed
     */
    private function renderReference($value, $reference)
    {
        if (is_array($reference[$value])) {
            return $reference[$value][0];
        }

        return $reference[$value];
    }
    /**
     * Retrieve a list of html buttons to use in the top panel (submit area).
     *
     * @param ModelInterface $parentModel The parent model.
     *
     * @return string
     */
    private function getParentModelButtons($parentModel)
    {
        if ('select' === $this->environment->getInputProvider()->getParameter('act')) {
            return '';
        }

        $config = $this->environment->getBaseConfigRegistry()->getBaseConfig();
        $this->environment->getView()->getPanel()->initialize($config);
        if (!$config->getSorting()) {
            return '';
        }

        $headerButtons = [];

        $headerButtons['editHeader'] = $this->getHeaderEditButton($parentModel);
        $headerButtons['pasteNew']   = $this->getHeaderPasteNewButton($parentModel);
        $headerButtons['pasteAfter'] = $this->getHeaderPasteTopButton($parentModel);

        return implode(' ', $headerButtons);
    }

    /**
     * Retrieve a list of html buttons to use in the top panel (submit area).
     *
     * @param ModelInterface $parentModel The parent model.
     *
     * @return null|string
     */
    protected function getHeaderEditButton(ModelInterface $parentModel)
    {
        $parentDefinition = $this->environment->getParentDataDefinition();
        /** @var CommandCollectionInterface $commands */
        $commands = $parentDefinition
            ->getDefinition(Contao2BackendViewDefinitionInterface::NAME)
            ->getModelCommands();

        if (!$parentDefinition->getBasicDefinition()->isEditable() || !$commands->hasCommandNamed('edit')) {
            return null;
        }

        $definition      = $this->environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        $parentName      = $basicDefinition->getParentDataProvider();
        $dispatcher      = $this->environment->getEventDispatcher();

        $command    = $commands->getCommandNamed('edit');
        $parameters = (array) $command->getParameters();

        // This should be set in command builder rather than here.
        $parameters['do']    = $this->environment->getInputProvider()->getParameter('do');
        $parameters['table'] = $parentName;
        $parameters['pid']   = '';

        $extra   = (array) $command->getExtra();
        $idParam = isset($extra['idparam']) ? $extra['idparam'] : null;
        if ($idParam) {
            $parameters[$idParam] = ModelId::fromModel($parentModel)->getSerialized();
        } else {
            $parameters['id'] = ModelId::fromModel($parentModel)->getSerialized();
        }

        if (null !== ($pid = $this->getGrandParentId($parentDefinition, $parentModel))) {
            if (false === $pid) {
                return null;
            }
            $parameters['pid'] = $pid;
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'edit.gif',
                $this->translate('editheader.0', $parentDefinition->getName())
            )
        );

        $href = '';
        foreach ($parameters as $key => $value) {
            $href .= sprintf('&%s=%s', $key, $value);
        }
        /** @var AddToUrlEvent $urlAfter */
        $urlAfter = $dispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, new AddToUrlEvent($href));

        return sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $urlAfter->getUrl(),
            StringUtil::specialchars(
                sprintf($this->translate('editheader.1', $parentDefinition->getName()), $parentModel->getId())
            ),
            $imageEvent->getHtml()
        );
    }

    /**
     * Retrieve the header button for paste new.
     *
     * @param ModelInterface $parentModel The parent model.
     *
     * @return null|string
     */
    private function getHeaderPasteNewButton(ModelInterface $parentModel)
    {
        $definition      = $this->environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        if (!$basicDefinition->isCreatable()) {
            return null;
        }

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        if ($parentDataProviderName = $basicDefinition->getParentDataProvider()) {
            $filter->andParentIsFromProvider($parentDataProviderName);
        } else {
            // FIXME: how can we ever end up here?
            $filter->andHasNoParent();
        }

        if ($this->environment->getClipboard()->isNotEmpty($filter)) {
            return null;
        }
        $dispatcher = $this->environment->getEventDispatcher();

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $dispatcher->dispatch(
            ContaoEvents::BACKEND_ADD_TO_URL,
            new AddToUrlEvent('act=create&amp;pid=' . ModelId::fromModel($parentModel)->getSerialized())
        );

        $parentDefinition = $this->environment->getParentDataDefinition();
        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'new.gif',
                $this->translate('pastenew.0', $parentDefinition->getName())
            )
        );

        return sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a>',
            $urlEvent->getUrl(),
            StringUtil::specialchars($this->translate('pastenew.0', $parentDefinition->getName())),
            $imageEvent->getHtml()
        );
    }

    /**
     * Retrieve the header button for paste new.
     *
     * @param ModelInterface $parentModel The parent model.
     *
     * @return null|string
     */
    private function getHeaderPasteTopButton(ModelInterface $parentModel)
    {
        $definition      = $this->environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();

        $filter = new Filter();
        $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
        $filter->andParentIsFromProvider($basicDefinition->getParentDataProvider());

        $clipboard = $this->environment->getClipboard();
        if ($clipboard->isEmpty($filter)) {
            return null;
        }

        $allowPasteTop = ViewHelpers::getManualSortingProperty($this->environment);

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

        $dispatcher = $this->environment->getEventDispatcher();
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
                    'pasteafter.gif',
                    $this->translate('pasteafter.0', $definition->getName()),
                    'class="blink"'
                )
            );

            return sprintf(
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
                'pasteafter_.gif',
                $this->translate('pasteafter.0', $definition->getName()),
                'class="blink"'
            )
        );

        return $imageEvent->getHtml();
    }

    /**
     * Obtain the id of the grand parent (if any).
     *
     * @param ContainerInterface $parentDefinition The parent definition.
     * @param ModelInterface     $parentModel      The parent model.
     *
     * @return null|string|false
     */
    private function getGrandParentId(ContainerInterface $parentDefinition, ModelInterface $parentModel)
    {
        if ('' == ($grandParentName = $parentDefinition->getBasicDefinition()->getParentDataProvider())) {
            return null;
        }

        $container = $this->environment->getDataDefinition();

        $relationship = $container->getModelRelationshipDefinition()->getChildCondition(
            $grandParentName,
            $parentDefinition->getName()
        );

        if (!$relationship) {
            return null;
        }
        $filter = $relationship->getInverseFilterFor($parentModel);

        $grandParentProvider = $this->environment->getDataProvider($grandParentName);

        $config = $grandParentProvider->getEmptyConfig();
        $config->setFilter($filter);

        $parents = $grandParentProvider->fetchAll($config);

        if ($parents->length() == 1) {
            return ModelId::fromModel($parents->get(0))->getSerialized();
        }
        return false;
    }
}
