<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Backend;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Picker\PickerConfig;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager;
use ContaoCommunityAlliance\DcGeneral\Controller\TreeNodeStates;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DCGE;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ModelFormatterConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DC\General;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Provide methods to handle input field "tableTree".
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class TreePicker extends Widget
{
    /**
     * Submit user input.
     *
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * The template.
     *
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * The sub template to use when generating.
     *
     * @var string
     */
    protected $subTemplate = 'widget_treepicker';

    /**
     * The ajax id.
     *
     * @var string
     */
    protected $strAjaxId;

    /**
     * The ajax key.
     *
     * @var string
     */
    protected $strAjaxKey;

    /**
     * The ajax name.
     *
     * @var string
     */
    protected $strAjaxName;

    /**
     * The data Container.
     *
     * @var General
     */
    protected $dataContainer;

    /**
     * The source data container name.
     *
     * @var string
     */
    protected $sourceName;

    /**
     * The field type to use.
     *
     * This may be either "radio" or "checkbox"
     *
     * @var string
     */
    protected $fieldType = 'radio';

    /**
     * Flag determining if the value shall always be saved.
     *
     * @var bool
     */
    protected $alwaysSave;

    /**
     * The minimum level for items to be selectable.
     *
     * @var int
     */
    protected $minLevel = 0;

    /**
     * The maximum level for items to be selectable.
     *
     * @var int
     */
    protected $maxLevel = 0;

    /**
     * The icon to use in the title section.
     *
     * @var string
     */
    protected $titleIcon = 'system/themes/flexible/icons/pagemounts.svg';

    /**
     * The title to display.
     *
     * @var string
     */
    protected $title;

    /**
     * The data container for the item source.
     *
     * @var General
     */
    protected $itemContainer;

    /**
     * The property used for ordering.
     *
     * @var string
     */
    protected $orderField;

    /**
     * The tree nodes to be handled.
     *
     * @var TreeNodeStates
     */
    protected $nodeStates;

    /**
     * Create a new instance.
     *
     * @param array   $attributes    The custom attributes.
     * @param General $dataContainer The data container.
     *
     * @throws DcGeneralRuntimeException When not in Backend mode.
     */
    public function __construct($attributes = [], General $dataContainer = null)
    {
        parent::__construct($attributes);

        $scopeDeterminator = System::getContainer()->get('cca.dc-general.scope-matcher');

        if ($scopeDeterminator->currentScopeIsUnknown() || !$scopeDeterminator->currentScopeIsBackend()) {
            throw new DcGeneralRuntimeException('Treepicker is currently for Backend only.');
        }

        $this->setUp($dataContainer);
    }

    /**
     * Setup all local values and create the dc instance for the referenced data source.
     *
     * @param General $dataContainer The data container to use.
     *
     * @return void
     */
    protected function setUp(General $dataContainer = null)
    {
        $this->dataContainer = $dataContainer ?: $this->objDca;

        if (!$this->dataContainer) {
            return;
        }

        $environment = $this->dataContainer->getEnvironment();

        if (!$this->sourceName) {
            $property = $environment
                ->getDataDefinition()
                ->getPropertiesDefinition()
                ->getProperty($environment->getInputProvider()->getValue('name'));

            foreach ($property->getExtra() as $k => $v) {
                $this->$k = $v;
            }

            $name           = $environment->getInputProvider()->getValue('name');
            $this->strField = $name;
            $this->strName  = $name;
            $this->strId    = $name;
            $this->label    = $property->getLabel() ?: $name;
            $this->strTable = $environment->getDataDefinition()->getName();
        }

        $this->itemContainer = (new DcGeneralFactory())
            ->setContainerName($this->sourceName)
            ->setTranslator($environment->getTranslator())
            ->setEventDispatcher($environment->getEventDispatcher())
            ->createDcGeneral();
    }

    /**
     * Update the value via ajax and redraw the widget.
     *
     * @param string  $ajaxAction    Not used in here.
     * @param General $dataContainer The data container to use.
     *
     * @return string
     *
     * @deprecated This method is deprecated use the update route.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.ExitExpression)
     *
     * @throws ResponseException Throws a response exception.
     */
    public function updateAjax($ajaxAction, $dataContainer)
    {
        if ('reloadGeneralTreePicker' !== $ajaxAction) {
            return '';
        }

        $this->setUp($dataContainer);
        $environment = $this->dataContainer->getEnvironment();
        $this->value = $environment->getInputProvider()->getValue('value');

        $label = $environment
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($this->strName)
            ->getDescription();

        $this->handleInputNameForEditAll();

        $result = '<input type="hidden" value="' . $this->strName . '" name="FORM_INPUTS[]">' .
                  '<h3><label>' . $this->label . '</label></h3>' . $this->generate();

        if (null !== $label && $GLOBALS['TL_CONFIG']['showHelp']) {
            $result .= '<p class="tl_help tl_tip">' . $label . '</p>';
        }

        throw new ResponseException(new Response($result));
    }

    /**
     * Retrieve the item container.
     *
     * @return General
     */
    public function getItemContainer()
    {
        return $this->itemContainer;
    }

    /**
     * Retrieve the environment of the item data container.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->getItemContainer()->getEnvironment();
    }

    /**
     * Create a tree states instance.
     *
     * @return TreeNodeStates
     */
    public function getTreeNodeStates()
    {
        if (!isset($this->nodeStates)) {
            $environment      = $this->getEnvironment();
            $sessionStorage   = $environment->getSessionStorage();
            $this->nodeStates = new TreeNodeStates(
                $sessionStorage->get($this->getToggleId()),
                $this->determineParentsOfValues()
            );

            // Maybe it is not the best location to do this here.
            if ('all' === $environment->getInputProvider()->getParameter('ptg')) {
                // Save in session and reload.
                $sessionStorage->set(
                    $this->getToggleId(),
                    $this->nodeStates->setAllOpen($this->nodeStates->isAllOpen())->getStates()
                );

                $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
            }
        }

        return $this->nodeStates;
    }

    /**
     * Add specific attributes.
     *
     * @param string $key   The key to set.
     * @param mixed  $value The value.
     *
     * @return void
     *
     * @throws \RuntimeException When an unknown field type is encountered.
     */
    public function __set($key, $value)
    {
        switch ($key) {
            case 'sourceName':
                $this->sourceName = $value;
                break;

            case 'fieldType':
                if (('radio' === $value) || ('checkbox' === $value)) {
                    $this->fieldType = $value;
                }
                break;

            case 'titleIcon':
                $this->titleIcon = $value;
                break;

            case 'mandatory':
                $this->arrConfiguration['mandatory'] = (bool) $value;
                break;

            case 'orderField':
                $this->orderField = $value;
                break;

            case 'value':
                $this->varValue = $this->widgetToValue($value);
                break;

            default:
                parent::__set($key, $value);
                break;
        }
    }

    /**
     * Convert the value according to the configured fieldtype.
     *
     * @param mixed $value The value to convert.
     *
     * @return mixed
     *
     * @throws \RuntimeException When an unknown field type is encountered.
     */
    private function convertValue($value)
    {
        if (empty($value)) {
            return null;
        }

        if (\is_array($value)) {
            return $value;
        }

        switch ($this->fieldType) {
            case 'radio':
                return $value;
            case 'checkbox':
                $delimiter = (false !== \stripos($value, "\t")) ? "\t" : ',';

                return StringUtil::trimsplit($delimiter, $value);
            default:
        }
        throw new \RuntimeException('Unknown field type encountered: ' . $this->fieldType);
    }

    /**
     * Return an object property.
     *
     * Supported keys:
     *
     * * sourceName: the name of the data container for the item elements.
     * * fieldType : the input type. Either "radio" or "checkbox".
     * * titleIcon:  the icon to use in the title section.
     * * mandatory:  the field value must not be empty.
     *
     * @param string $key The property name.
     *
     * @return string The property value.
     */
    public function __get($key)
    {
        switch ($key) {
            case 'sourceName':
                return $this->sourceName;

            case 'fieldType':
                return $this->fieldType;

            case 'titleIcon':
                return $this->titleIcon;

            case 'mandatory':
                return $this->arrConfiguration['mandatory'];

            case 'orderField':
                return $this->orderField;

            case 'dataContainer':
                return $this->dataContainer;

            default:
        }
        return parent::__get($key);
    }

    /**
     * Skip the field if "change selection" is not checked.
     *
     * @param array $value The current value.
     *
     * @return array|string
     */
    protected function validator($value)
    {
        $convertValue = $this->widgetToValue($value);

        if ((null === $convertValue) && $this->mandatory) {
            $translator = $this->getEnvironment()->getTranslator();

            $message = empty($this->label)
                ? $translator->translate('ERR.mdtryNoLabel')
                : \sprintf(
                    $translator->translate('ERR.mandatory'),
                    $this->strLabel
                );

            $this->addError($message);
        }

        return $convertValue;
    }

    /**
     * Render the current values for listing.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function renderItemsPlain()
    {
        $values     = [];
        $value      = $this->varValue;
        $idProperty = $this->idProperty ?: 'id';

        if ('radio' === $this->fieldType && !empty($value)) {
            $value = (array) $value;
        }

        if (\is_array($value) && !empty($value)) {
            $environment = $this->getEnvironment();
            $dataDriver  = $environment->getDataProvider();
            $config      = $environment->getBaseConfigRegistry()->getBaseConfig();
            $filter      = FilterBuilder::fromArrayForRoot()
                ->getFilter()
                ->andPropertyValueIn($idProperty, $value)
                ->getAllAsArray();

            $config->setFilter($filter);

            // Set the sort field.
            if ($this->orderField && $dataDriver->fieldExists($this->orderField)) {
                $config->setSorting([$this->orderField => 'ASC']);
            }

            foreach ($dataDriver->fetchAll($config) as $model) {
                $formatted        = $this->formatModel($model, false);
                $idValue          = $model->getProperty($idProperty);
                $values[$idValue] = $formatted[0]['content'];
            }

            // Apply a custom sort order.
            $values = $this->sortValues($values);
        }

        return $values;
    }

    /**
     * Sort the passed value array by the defined order field (if defined).
     *
     * @param array $values The values.
     *
     * @return array
     */
    private function sortValues($values)
    {
        if (!($this->orderField && \is_array($this->{$this->orderField}))) {
            return $values;
        }
        /** @var array $orderValues */
        $orderValues = $this->{$this->orderField};
        $result      = [];
        foreach ($orderValues as $i) {
            if (isset($values[$i])) {
                $result[$i] = $values[$i];
                unset($values[$i]);
            }
        }
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    /**
     * Generate the widget and return it as string.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function generate()
    {
        $GLOBALS['TL_JAVASCRIPT']['cca.dc-general.vanillaGeneral'] = 'bundles/ccadcgeneral/js/vanillaGeneral.js';

        $environment = $this->getEnvironment();
        $translator  = $environment->getTranslator();
        $template    = new ContaoBackendViewTemplate('widget_treepicker');

        $icon = new GenerateHtmlEvent($this->titleIcon);
        $environment->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $icon);

        $template
            ->setTranslator($translator)
            ->set('id', $this->strId)
            ->set('name', $this->strName)
            ->set('class', ($this->strClass ? ' ' . $this->strClass : ''))
            ->set('icon', $icon->getHtml())
            ->set('title', $translator->translate($this->title ?: 'MSC.treePicker', '', [$this->sourceName]))
            ->set('changeSelection', $translator->translate('MSC.changeSelection'))
            ->set('dragItemsHint', $translator->translate('MSC.dragItemsHint'))
            ->set('fieldType', $this->fieldType)
            ->set('values', $this->renderItemsPlain())
            ->set('label', $this->label)
            ->set('popupUrl', $this->generatePickerUrl())
            ->set('updateUrl', $this->generateUpdateUrl())
            ->set('providerName', $this->sourceName);

        $this->addOrderFieldToTemplate($template);

        // Load the fonts for the drag hint.
        $GLOBALS['TL_CONFIG']['loadGoogleFonts'] = true;

        return $template->parse();
    }

    /**
     * Generate the picker url.
     *
     * @return string
     */
    protected function generatePickerUrl()
    {
        $parameter = [
            'fieldType'    => $this->fieldType,
            'sourceName'   => $this->sourceName,
            'modelId'      => ModelId::fromModel($this->dataContainer->getModel())->getSerialized(),
            'orderField'   => $this->orderField,
            'propertyName' => $this->name
        ];

        if ($this->pickerOrderProperty && $this->pickerSortDirection) {
            $parameter = \array_merge(
                $parameter,
                [
                    'orderProperty' => $this->pickerOrderProperty,
                    'sortDirection' => $this->pickerSortDirection
                ]
            );
        }

        return System::getContainer()->get('contao.picker.builder')->getUrl('cca_tree', $parameter);
    }

    /**
     * Convert the value from widget for internal process.
     *
     * @param mixed $value The widget value.
     *
     * @return null|string|array
     */
    public function widgetToValue($value)
    {
        return $this->convertValue($value);
    }

    /**
     * Convert the value for the widget.
     *
     * @param mixed $value The input value.
     *
     * @return string
     *
     * @throws \RuntimeException Throws an exception, if unknown field type encountered.
     */
    public function valueToWidget($value)
    {
        if (!\in_array($this->fieldType, ['radio', 'checkbox'])) {
            throw new \RuntimeException('Unknown field type encountered: ' . $this->fieldType);
        }

        if (null === $value) {
            return '';
        }

        return ('radio' === $this->fieldType) ? $value : \implode(',', $value);
    }

    /**
     * Generate the update url.
     *
     * @return string
     */
    protected function generateUpdateUrl()
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        $configPicker = new PickerConfig(
            'cca_tree',
            [
                'fieldType'    => $this->fieldType,
                'sourceName'   => $this->sourceName,
                'modelId'      => ModelId::fromModel($this->dataContainer->getModel())->getSerialized(),
                'orderField'   => $this->orderField,
                'propertyName' => $this->name
            ],
            $this->valueToWidget($this->value)
        );

        return System::getContainer()->get('router')->generate(
            'cca_dc_general_tree_update',
            [
                'picker' => $configPicker->cloneForCurrent((string) $request->query->get('context'))->urlEncode()
            ],
            UrlGenerator::ABSOLUTE_URL
        );
    }

    /**
     * Generate the breadcrumb url.
     *
     * @param ModelInterface $model The model.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function generateBreadCrumbUrl(ModelInterface $model)
    {
        $toggleUrlEvent = new AddToUrlEvent(
            'ptg=' . $model->getId() . '&amp;provider=' . $model->getProviderName()
        );
        $this->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $toggleUrlEvent);

        return System::getContainer()->get('router')->generate(
            'cca_dc_general_tree_breadcrumb',
            $this->getQueryParameterFromUrl($toggleUrlEvent->getUrl()),
            UrlGenerator::ABSOLUTE_URL
        );
    }

    /**
     * Generate the toggle url.
     *
     * @param ModelInterface $model The model.
     *
     * @return string
     */
    private function generateToggleUrl(ModelInterface $model)
    {
        $toggleUrlEvent = new AddToUrlEvent(
            'ptg=' . $model->getId() . '&amp;provider=' . $model->getProviderName()
        );
        $this->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $toggleUrlEvent);

        return System::getContainer()->get('router')->generate(
            'cca_dc_general_tree_toggle',
            $this->getQueryParameterFromUrl($toggleUrlEvent->getUrl()),
            UrlGenerator::ABSOLUTE_URL
        );
    }

    /**
     * Get the query parameters from url.
     *
     * @param string $url The url.
     *
     * @return array
     */
    private function getQueryParameterFromUrl($url)
    {
        $parameters = array();
        foreach (\preg_split('/&(amp;)?/i', \preg_split('/[?]/ui', $url)[1]) as $value) {
            $chunks                 = \explode('=', $value);
            $parameters[$chunks[0]] = $chunks[1];
        }

        return $parameters;
    }

    /**
     * Add the order field to the template, if the picker has order.
     *
     * @param ContaoBackendViewTemplate $template The template.
     *
     * @return void
     */
    private function addOrderFieldToTemplate(ContaoBackendViewTemplate $template)
    {
        if (!$this->orderField) {
            return;
        }

        $translator = $this->getEnvironment()->getTranslator();

        $template
            ->set('hasOrder', true)
            ->set('orderId', $this->orderField)
            ->set('orderName', $this->orderName)
            ->set('orderValue', \implode(',', (array) $this->value))
            ->set('changeSelection', $translator->translate('MSC.changeSelection'))
            ->set('dragItemsHint', $translator->translate('MSC.dragItemsHint'));
    }

    /**
     * Generate when being called from within a popup.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function generatePopup()
    {
        $GLOBALS['TL_JAVASCRIPT']['cca.dc-general.vanillaGeneral'] = 'bundles/ccadcgeneral/js/vanillaGeneral.js';

        $environment = $this->getEnvironment();
        $translator  = $environment->getTranslator();
        $template    = new ContaoBackendViewTemplate('widget_treepicker_popup');

        $icon = new GenerateHtmlEvent($this->titleIcon);
        $environment->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $icon);

        $template
            ->setTranslator($translator)
            ->set('id', 'tl_listing')
            ->set('name', $this->strName)
            ->set('class', ($this->strClass ? ' ' . $this->strClass : ''))
            ->set('icon', $icon->getHtml())
            ->set('title', $translator->translate($this->title ?: 'MSC.treePicker', '', [$this->sourceName]))
            ->set('fieldType', $this->fieldType)
            ->set('resetSelected', $translator->translate('MSC.resetSelected'))
            ->set('selectAll', $translator->translate('MSC.selectAll'))
            ->set('values', StringUtil::deserialize($this->varValue, true));

        // Create Tree Render with custom root points.
        $tree = '';
        foreach ($this->getRootIds() as $pid) {
            $tree .= $this->generateTreeView($this->loadCollection($pid), 'tree');
        }

        $template->set('tree', $tree);

        // Load the fonts for the drag hint.
        $GLOBALS['TL_CONFIG']['loadGoogleFonts'] = true;

        return $template->parse();
    }

    /**
     * Determine the root ids.
     *
     * @return array
     */
    private function getRootIds()
    {
        $root = $this->root;
        $root = \is_array($root) ? $root : ((\is_numeric($root) && $root > 0) ? [$root] : []);
        $root = \array_merge($root, [0]);

        return $root;
    }

    /**
     * Generate a particular sub part of the page tree and return it as HTML string.
     *
     * @return string
     */
    public function generateAjax()
    {
        $input = $this->getEnvironment()->getInputProvider();

        if ($input->hasValue('action')
            && ('DcGeneralLoadSubTree' === $input->getValue('action'))
        ) {
            $provider = $input->getValue('providerName');
            $rootId   = $input->getValue('id');
            $this->getEnvironment()->getSessionStorage()->set(
                $this->getToggleId(),
                $this->getTreeNodeStates()->toggleModel($provider, $rootId)->getStates()
            );
            $collection = $this->loadCollection($rootId, ((int) $input->getValue('level') + 1));
            return $this->generateTreeView($collection, 'tree');
        }

        return '';
    }

    /**
     * Retrieve the id for this view.
     *
     * @return string
     */
    protected function getToggleId()
    {
        return $this->getEnvironment()->getDataDefinition()->getName() . $this->strId . '_tree';
    }

    /**
     * Retrieve the id for this view.
     *
     * @return string
     */
    public function getSearchSessionKey()
    {
        return $this->getEnvironment()->getDataDefinition()->getName() . $this->strId . '_tree_search';
    }

    /**
     * Check if an custom sorting field has been defined.
     *
     * @return bool
     */
    public function isSearchAvailable()
    {
        return !empty($this->searchField);
    }

    /**
     * Check the state of a model and set the metadata accordingly.
     *
     * @param ModelInterface $model The model of which the state shall be checked of.
     * @param int            $level The tree level the model is contained within.
     *
     * @return void
     */
    protected function determineModelState(ModelInterface $model, $level)
    {
        $model->setMeta(DCGE::TREE_VIEW_LEVEL, $level);
        $model->setMeta(
            $model::SHOW_CHILDREN,
            $this->getTreeNodeStates()->isModelOpen(
                $model->getProviderName(),
                $model->getId()
            )
        );
    }

    /**
     * This "renders" a model for tree view.
     *
     * @param ModelInterface $model     The model to render.
     * @param int            $level     The current level in the tree hierarchy.
     * @param array          $subTables The names of data providers that shall be rendered "below" this item.
     *
     * @return void
     */
    protected function treeWalkModel(ModelInterface $model, $level, $subTables = [])
    {
        $environment   = $this->getEnvironment();
        $relationships = $environment->getDataDefinition()->getModelRelationshipDefinition();
        $hasChild      = false;

        $this->determineModelState($model, ($level - 1));

        $childCollections = [];
        foreach ($subTables as $subTable) {
            // Evaluate the child filter for this item.
            $childFilter = $relationships->getChildCondition($model->getProviderName(), $subTable);

            // If we do not know how to render this table within here, continue with the next one.
            if (!$childFilter) {
                continue;
            }

            // Create a new Config and fetch the children from the child provider.
            $childConfig = $environment->getDataProvider($subTable)->getEmptyConfig();
            $childConfig->setFilter($childFilter->getFilter($model));

            $childConfig->setSorting(['sorting' => 'ASC']);

            $childCollection = $environment->getDataProvider($subTable)->fetchAll($childConfig);

            $hasChild = ($childCollection->length() > 0);

            // Speed up - we may exit if we have at least one child but the parenting model is collapsed.
            if ($hasChild && !$model->getMeta($model::SHOW_CHILDREN)) {
                break;
            }

            if ($hasChild) {
                $this->treeWalkChildCollection($childCollection, $model, $level);

                $childCollections[] = $childCollection;

                // Speed up, if collapsed, one item is enough to break as we have some children.
                if (!$model->getMeta($model::SHOW_CHILDREN)) {
                    break;
                }
            }
        }

        // If expanded, store children.
        if ($model->getMeta($model::SHOW_CHILDREN) && (\count($childCollections))) {
            $model->setMeta($model::CHILD_COLLECTIONS, $childCollections);
        }

        $model->setMeta($model::HAS_CHILDREN, $hasChild);
    }

    /**
     * Walk in the child collection for the tree.
     *
     * @param CollectionInterface $childCollection The child collection.
     * @param ModelInterface      $model           The model to render.
     * @param int                 $level           The current level in the tree hierarchy.
     *
     * @return void
     */
    private function treeWalkChildCollection(CollectionInterface $childCollection, ModelInterface $model, $level)
    {
        $relationships = $this->getEnvironment()->getDataDefinition()->getModelRelationshipDefinition();

        foreach ($childCollection as $childModel) {
            // Let the child know about it's parent.
            $model->setMeta($model::PARENT_ID, $model->getId());
            $model->setMeta($model::PARENT_PROVIDER_NAME, $model->getProviderName());

            $mySubTables = [];
            foreach ($relationships->getChildConditions($model->getProviderName()) as $condition) {
                $mySubTables[] = $condition->getDestinationName();
            }

            $this->treeWalkModel($childModel, ($level + 1), $mySubTables);
        }
    }

    /**
     * Recursively retrieve a collection of all complete node hierarchy.
     *
     * @param array  $rootId       The ids of the root node.
     * @param int    $level        The level the items are residing on.
     * @param string $providerName The data provider from which the root element originates from.
     *
     * @return CollectionInterface
     */
    public function getTreeCollectionRecursive($rootId, $level = 0, $providerName = null)
    {
        $environment   = $this->getEnvironment();
        $dataDriver    = $environment->getDataProvider($providerName);
        $tableTreeData = $dataDriver->getEmptyCollection();
        $rootConfig    = $environment->getBaseConfigRegistry()->getBaseConfig();
        $relationships = $environment->getDataDefinition()->getModelRelationshipDefinition();

        if (!$rootId) {
            $this->prepareFilterForRootCondition();
            $this->pushRootModelToTreeCollection($dataDriver, $tableTreeData, $level);

            return $tableTreeData;
        }

        $rootConfig->setId($rootId);
        // Fetch root element.
        $rootModel = $dataDriver->fetch($rootConfig);

        $mySubTables = [];
        foreach ($relationships->getChildConditions($rootModel->getProviderName()) as $condition) {
            $mySubTables[] = $condition->getDestinationName();
        }

        $this->treeWalkModel($rootModel, $level, $mySubTables);
        $rootCollection = $dataDriver->getEmptyCollection();
        $rootCollection->push($rootModel);

        return $rootCollection;
    }

    /**
     * Prepare filter if has root condition.
     *
     * @return ConfigInterface
     */
    private function prepareFilterForRootCondition()
    {
        $environment   = $this->getEnvironment();
        $rootCondition = $environment->getDataDefinition()->getModelRelationshipDefinition()->getRootCondition();

        $baseConfig = $environment->getBaseConfigRegistry()->getBaseConfig();
        if (!$rootCondition) {
            return $baseConfig;
        }

        $baseFilter = $baseConfig->getFilter();
        $filter     = $rootCondition->getFilterArray();

        if ($baseFilter) {
            $filter = \array_merge($baseFilter, $filter);
        }

        $baseConfig->setFilter($filter);

        return $baseConfig;
    }

    /**
     * Push root model to the tree collection.
     *
     * @param DataProviderInterface $dataProvider   The data provider.
     * @param CollectionInterface   $treeCollection The tree collection.
     * @param int                   $level          The level the items are residing on.
     *
     * @return void
     */
    private function pushRootModelToTreeCollection(
        DataProviderInterface $dataProvider,
        CollectionInterface $treeCollection,
        $level
    ) {
        $environment   = $this->getEnvironment();
        $inputProvider = $environment->getInputProvider();
        $baseConfig    = $this->prepareFilterForRootCondition();
        $relationships = $environment->getDataDefinition()->getModelRelationshipDefinition();

        if ($inputProvider->hasParameter('orderProperty') && $inputProvider->hasParameter('sortDirection')) {
            $orderProperty = $inputProvider->getParameter('orderProperty');
            $sortDirection = $inputProvider->getParameter('sortDirection');

            $baseConfig->setSorting([$orderProperty => $sortDirection]);
        }

        // Fetch all root elements.
        $collection = $dataProvider->fetchAll($baseConfig);
        if (!$collection->count()) {
            return;
        }

        $mySubTables = [];
        foreach ($relationships->getChildConditions($collection->get(0)->getProviderName()) as $condition) {
            $mySubTables[] = $condition->getDestinationName();
        }

        foreach ($collection as $model) {
            /** @var ModelInterface $model */
            $treeCollection->push($model);
            $this->treeWalkModel($model, ($level + 1), $mySubTables);
        }
    }

    /**
     * Load the collection of child items and the parent item for the currently selected parent item.
     *
     * @param mixed $rootId       The root element (or null to fetch everything).
     * @param int   $level        The current level in the tree (of the optional root element).
     * @param null  $providerName The data provider from which the optional root element shall be taken from.
     *
     * @return CollectionInterface
     */
    public function loadCollection($rootId = null, $level = 0, $providerName = null)
    {
        $environment = $this->getEnvironment();

        $collection = $this->getTreeCollectionRecursive($rootId, $level, $providerName);

        if ($rootId) {
            $treeData = $environment->getDataProvider($providerName)->getEmptyCollection();
            $model    = $collection->get(0);
            foreach ($model->getMeta($model::CHILD_COLLECTIONS) as $collection) {
                foreach ($collection as $subModel) {
                    $treeData->push($subModel);
                }
            }
            return $treeData;
        }

        return $collection;
    }

    /**
     * Retrieve the formatter for the given model.
     *
     * @param ModelInterface $model    The model for which the formatter shall be retrieved.
     * @param bool           $treeMode Flag if we are running in tree mode or not.
     *
     * @return ModelFormatterConfigInterface
     */
    protected function getFormatter(ModelInterface $model, $treeMode)
    {
        /** @var ListingConfigInterface $listing */
        $definition = $this->getEnvironment()->getDataDefinition();
        $listing    = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME)->getListingConfig();

        if ($listing->hasLabelFormatter($model->getProviderName())) {
            return $listing->getLabelFormatter($model->getProviderName());
        }

        // If not in tree mode and custom label has been defined, use it.
        if (!$treeMode && $this->itemLabel) {
            $label     = (array) $this->itemLabel;
            $formatter = new DefaultModelFormatterConfig();
            $formatter->setPropertyNames($label['fields']);
            $formatter->setFormat($label['format']);
            $formatter->setMaxLength($label['maxCharacters']);
            return $formatter;
        }

        // If no label has been defined, use some default.
        $properties = [];
        foreach ($definition->getPropertiesDefinition()->getProperties() as $property) {
            if ('text' === $property->getWidgetType()) {
                $properties[] = $property->getName();
            }
        }

        return (new DefaultModelFormatterConfig())
            ->setPropertyNames($properties)
            ->setFormat(\str_repeat('%s ', \count($properties)));
    }

    /**
     * Format a model accordingly to the current configuration.
     *
     * Returns either an array when in tree mode or a string in (parented) list mode.
     *
     * @param ModelInterface $model    The model that shall be formatted.
     * @param bool           $treeMode Flag if we are running in tree mode or not (optional, default: true).
     *
     * @return array
     */
    public function formatModel(ModelInterface $model, $treeMode = true)
    {
        /** @var ListingConfigInterface $listing */
        $environment  = $this->getEnvironment();
        $definition   = $environment->getDataDefinition();
        $listing      = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME)->getListingConfig();
        $properties   = $definition->getPropertiesDefinition();
        $firstSorting = \reset(\array_keys((array) $listing->getDefaultSortingFields()));
        $formatter    = $this->getFormatter($model, $treeMode);

        $arguments = [];
        foreach ($formatter->getPropertyNames() as $propertyName) {
            if ($properties->hasProperty($propertyName)) {
                $arguments[$propertyName] = (string) $model->getProperty($propertyName);
            } else {
                $arguments[$propertyName] = '-';
            }
        }

        $event = new ModelToLabelEvent($environment, $model);
        $event
            ->setArgs($arguments)
            ->setLabel($formatter->getFormat())
            ->setFormatter($formatter);

        $environment->getEventDispatcher()->dispatch($event::NAME, $event);

        $labelList = [];
        $this->prepareLabelWithDisplayedProperties($formatter, $event->getArgs(), $firstSorting, $labelList);
        $this->prepareLabelWithOutDisplayedProperties($formatter, $event->getArgs(), $event->getLabel(), $labelList);

        return $labelList;
    }

    /**
     * Prepare labels for display with properties.
     *
     * @param ModelFormatterConfigInterface $formatter    The model formatter.
     * @param array                         $arguments    The model label arguments.
     * @param string|bool                   $firstSorting The first sorting.
     * @param array                         $labelList    The label list.
     *
     * @return void
     */
    private function prepareLabelWithDisplayedProperties(
        ModelFormatterConfigInterface $formatter,
        array $arguments,
        $firstSorting,
        array &$labelList
    ) {
        $definition = $this->getEnvironment()->getDataDefinition();
        $listing    = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME)->getListingConfig();
        if (!$listing->getShowColumns()) {
            return;
        }

        $fieldList = $formatter->getPropertyNames();

        if (!\is_array($arguments)) {
            $labelList[] = [
                'colspan' => \count($fieldList),
                'class'   => 'tl_file_list col_1',
                'content' => $arguments
            ];
        } else {
            foreach ($fieldList as $j => $propertyName) {
                $labelList[] = [
                    'colspan' => 1,
                    'class'   => 'tl_file_list col_' . $j . (($propertyName === $firstSorting) ? ' ordered_by' : ''),
                    'content' => ('' !== $arguments[$propertyName]) ? $arguments[$propertyName] : '-'
                ];
            }
        }
    }

    /**
     * Prepare labels for display without properties.
     *
     * @param ModelFormatterConfigInterface $formatter The model formatter.
     * @param array                         $arguments The model label arguments.
     * @param string                        $label     The label for format.
     * @param array                         $labelList The label list.
     *
     * @return void
     */
    private function prepareLabelWithOutDisplayedProperties(
        ModelFormatterConfigInterface $formatter,
        array $arguments,
        $label,
        array &$labelList
    ) {
        $definition = $this->getEnvironment()->getDataDefinition();
        $listing    = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME)->getListingConfig();
        if ($listing->getShowColumns()) {
            return;
        }

        if (!\is_array($arguments)) {
            $string = $arguments;
        } else {
            $string = \vsprintf($label, $arguments);
        }

        if (($maxLength = null !== $formatter->getMaxLength()) && \strlen($string) > $maxLength) {
            $string = \substr($string, 0, $maxLength);
        }

        $labelList[] = [
            'colspan' => null,
            'class'   => 'tl_file_list',
            'content' => $string
        ];
    }

    /**
     * Render a given model.
     *
     * @param ModelInterface $model    The model to render.
     * @param string         $toggleID The id of the toggler.
     *
     * @return string
     */
    protected function parseModel($model, $toggleID)
    {
        $model->setMeta($model::LABEL_VALUE, $this->formatModel($model));

        if ($model->getMeta($model::SHOW_CHILDREN)) {
            $toggleTitle = $this->getEnvironment()->getTranslator()->translate('collapseNode', 'MSC');
        } else {
            $toggleTitle = $this->getEnvironment()->getTranslator()->translate('expandNode', 'MSC');
        }

        $toggleScript = \sprintf(
            'Backend.getScrollOffset(); return BackendGeneral.loadSubTree(this, ' .
            '{\'toggler\':\'%s\', \'id\':\'%s\', \'providerName\':\'%s\', \'level\':\'%s\', \'url\':\'%s\'});',
            $toggleID,
            $model->getId(),
            $model->getProviderName(),
            $model->getMeta('dc_gen_tv_level'),
            $this->generateToggleUrl($model)
        );

        $template = new ContaoBackendViewTemplate('widget_treepicker_entry');
        $template
            ->setTranslator($this->getEnvironment()->getTranslator())
            ->set('id', $this->strId)
            ->set('name', $this->strName)
            ->set('theme', Backend::getTheme())
            ->set('fieldType', $this->fieldType)
            ->set('environment', $this->getEnvironment())
            ->set('objModel', $model)
            ->set('strToggleID', $toggleID)
            ->set('toggleUrl', $this->generateToggleUrl($model))
            ->set('toggleTitle', $toggleTitle)
            ->set('toggleScript', $toggleScript)
            ->set('active', static::optionChecked($model->getProperty($this->idProperty), $this->value))
            ->set('idProperty', $this->idProperty);

        $level = $model->getMeta(DCGE::TREE_VIEW_LEVEL);
        if (($this->minLevel > 0) && ($level < ($this->minLevel - 1))) {
            $template->set('fieldType', 'none');
        }
        if (($this->maxLevel > 0) && ($level > ($this->maxLevel - 1))) {
            $template->set('fieldType', 'none');
        }

        return $template->parse();
    }

    /**
     * Generate the tree view for a given collection.
     *
     * @param CollectionInterface $collection The collection to iterate over.
     * @param string              $treeClass  The class to use for the tree.
     *
     * @return string
     */
    protected function generateTreeView($collection, $treeClass)
    {
        $content = [];
        foreach ($collection as $model) {
            /** @var ModelInterface $model */

            $toggleID = $model->getProviderName() . '_' . $treeClass . '_' . $model->getId();

            $content[] = $this->parseModel($model, $toggleID);

            if ($model->getMeta($model::HAS_CHILDREN) && $model->getMeta($model::SHOW_CHILDREN)) {
                $template = new ContaoBackendViewTemplate('widget_treepicker_child');
                $subHtml  = '';

                foreach ($model->getMeta($model::CHILD_COLLECTIONS) as $objChildCollection) {
                    $subHtml .= $this->generateTreeView($objChildCollection, $treeClass);
                }

                $template
                    ->setTranslator($this->getEnvironment()->getTranslator())
                    ->set('objParentModel', $model)
                    ->set('strToggleID', $toggleID)
                    ->set('strHTML', $subHtml)
                    ->set('strTable', $model->getProviderName());

                $content[] = $template->parse();
            }
        }

        return \implode("\n", $content);
    }


    /**
     * Fetch all parents of the passed model.
     *
     * @param ModelInterface $model   The model.
     * @param string[]       $parents The ids of all detected parents so far.
     *
     * @return void
     */
    private function parentsOf($model, &$parents)
    {
        $environment    = $this->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        $collector      = new ModelCollector($this->getEnvironment());
        $relationships  = new RelationshipManager(
            $dataDefinition->getModelRelationshipDefinition(),
            $dataDefinition->getBasicDefinition()->getMode()
        );

        if (!$relationships->isRoot($model)) {
            $parent = $collector->searchParentOf($model);
            if (!isset($parents[$model->getProviderName()][$parent->getId()])) {
                $this->parentsOf($parent, $parents);
            }
        }

        $parents[$model->getProviderName()][$model->getId()] = 1;
    }

    /**
     * Determine all parents of all selected values.
     *
     * @return array
     */
    private function determineParentsOfValues()
    {
        $parents     = [];
        $environment = $this->getEnvironment();
        $mode        = $environment->getDataDefinition()->getBasicDefinition()->getMode();
        if (BasicDefinitionInterface::MODE_HIERARCHICAL !== $mode) {
            return [];
        }

        foreach ((array) $this->varValue as $value) {
            $dataDriver = $environment->getDataProvider();
            $this->parentsOf($dataDriver->fetch($dataDriver->getEmptyConfig()->setId($value)), $parents);
        }

        return $parents;
    }

    /**
     * Handle the input name for edit all mode.
     *
     * @return void
     */
    private function handleInputNameForEditAll()
    {
        if (('select' !== $this->getEnvironment()->getInputProvider()->getParameter('act'))
            && ('edit' !== $this->getEnvironment()->getInputProvider()->getParameter('select'))
            && ('edit' !== $this->getEnvironment()->getInputProvider()->getParameter('mode'))
        ) {
            return;
        }

        $tableName      = \explode('____', $this->getEnvironment()->getInputProvider()->getValue('name'))[0];
        $sessionKey     = 'DC_GENERAL_' . \strtoupper($tableName);
        $sessionStorage =
            System::getContainer()->get('cca.dc-general.session_factory')->createService()->setScope($sessionKey);

        $selectAction = $this->getEnvironment()->getInputProvider()->getParameter('select');

        $session = $sessionStorage->get($tableName . '.' . $selectAction);

        $originalPropertyName = null;
        foreach ((array) $session['models'] as $modelId) {
            if (null !== $originalPropertyName) {
                break;
            }

            $propertyNamePrefix = \str_replace('::', '____', $modelId) . '_';
            if (0 !== strpos($this->strName, $propertyNamePrefix)) {
                continue;
            }

            $originalPropertyName = \substr($this->strName, \strlen($propertyNamePrefix));
        }

        if (!$originalPropertyName) {
            return;
        }

        $this->arrConfiguration['originalField'] = $originalPropertyName;

        $this->strName = $propertyNamePrefix . '[' . $originalPropertyName . ']';
    }
}
