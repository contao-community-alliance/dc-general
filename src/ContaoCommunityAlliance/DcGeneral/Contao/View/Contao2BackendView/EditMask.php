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
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\BackendUser;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreEditModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class manages the displaying of the edit/create mask containing the widgets.
 *
 * It also handles the persisting of the model.
 */
class EditMask
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * The model to be manipulated.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * The original model from the database.
     *
     * @var ModelInterface
     */
    protected $originalModel;

    /**
     * The method to be executed before the model is persisted.
     *
     * @var callable|null
     */
    protected $preFunction;

    /**
     * The method to be executed after the model is persisted.
     *
     * @var callable|null
     */
    protected $postFunction;

    /**
     * The rendered breadcrumb.
     *
     * @var string
     */
    protected $breadcrumb;

    /**
     * Create the edit mask.
     *
     * @param BackendViewInterface $view          The view in use.
     *
     * @param ModelInterface       $model         The model with the current data.
     *
     * @param ModelInterface       $originalModel The data from the original data.
     *
     * @param callable             $preFunction   The function to call before saving an item.
     *
     * @param callable             $postFunction  The function to call after saving an item.
     *
     * @param string               $breadcrumb    The rendered breadcrumb.
     */
    public function __construct($view, $model, $originalModel, $preFunction, $postFunction, $breadcrumb)
    {
        $this->environment   = $view->getEnvironment();
        $this->model         = $model;
        $this->originalModel = $originalModel;
        $this->preFunction   = $preFunction;
        $this->postFunction  = $postFunction;
        $this->breadcrumb    = $breadcrumb;
    }

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Retrieve the data definition from the environment.
     *
     * @return ContainerInterface
     */
    protected function getDataDefinition()
    {
        return $this->getEnvironment()->getDataDefinition();
    }

    /**
     * Determines if this view is opened in a popup frame.
     *
     * @return bool
     */
    protected function isPopup()
    {
        return $this->getEnvironment()->getInputProvider()->getParameter('popup');
    }

    /**
     * Ensure the view is editable and throw an Exception if not.
     *
     * @param ModelInterface $model The model to be edited.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When the definition is not editable.
     */
    protected function checkEditable($model)
    {
        $environment = $this->getEnvironment();
        $definition  = $this->getDataDefinition();

        // Check if table is editable.
        if ($model->getId() && !$definition->getBasicDefinition()->isEditable()) {
            $message = 'DataContainer ' . $definition->getName() . ' is not editable';
            $environment->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent($message, TL_ERROR, 'DC_General - edit()')
            );
            throw new DcGeneralRuntimeException($message);
        }
    }

    /**
     * Ensure the view is editable and throw an Exception if not.
     *
     * @param ModelInterface $model The model to be edited, if this is given, we are not in create mode.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException When the definition is not editable.
     */
    protected function checkCreatable($model)
    {
        $environment = $this->getEnvironment();
        $definition  = $this->getDataDefinition();

        // Check if table is closed but we are adding a new item.
        if (!($model->getId() || $definition->getBasicDefinition()->isCreatable())) {
            $message = 'DataContainer ' . $definition->getName() . ' is closed';
            $environment->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent($message, TL_ERROR, 'DC_General - edit()')
            );
            throw new DcGeneralRuntimeException($message);
        }
    }

    /**
     * Ensure a property is defined in the data definition and raise an exception if it is unknown.
     *
     * @param string                        $property            The property name to check.
     *
     * @param PropertiesDefinitionInterface $propertyDefinitions The property definitions.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException When the property is not registered in the definition.
     */
    protected function ensurePropertyExists($property, $propertyDefinitions)
    {
        if (!$propertyDefinitions->hasProperty($property)) {
            throw new DcGeneralInvalidArgumentException(
                sprintf(
                    'Property %s is mentioned in palette but not defined in propertyDefinition.',
                    $property
                )
            );
        }
    }

    /**
     * Process input and return all modified properties or null if there is no input.
     *
     * @param ContaoWidgetManager $widgetManager The widget manager in use.
     *
     * @return null|PropertyValueBag
     */
    protected function processInput($widgetManager)
    {
        $input = $this->getEnvironment()->getInputProvider();

        if ($input->getValue('FORM_SUBMIT') == $this->getDataDefinition()->getName()) {
            $propertyValues = new PropertyValueBag();
            $propertyNames  = array_intersect(
                $this->getDataDefinition()->getPropertiesDefinition()->getPropertyNames(),
                (array) $input->getValue('FORM_INPUTS')
            );

            // Process input and update changed properties.
            foreach ($propertyNames as $propertyName) {
                $propertyValue = $input->hasValue($propertyName) ? $input->getValue($propertyName, true) : null;
                $propertyValues->setPropertyValue($propertyName, $propertyValue);
            }

            $widgetManager->processInput($propertyValues);

            return $propertyValues;
        }

        return null;
    }

    /**
     * Trigger the pre persist event and handle the prePersist function if available.
     *
     * @return void
     */
    protected function handlePrePersist()
    {
        if ($this->preFunction !== null) {
            call_user_func($this->preFunction, $this->getEnvironment(), $this->model, $this->originalModel);
        }

        $this->getEnvironment()->getEventDispatcher()->dispatch(
            PrePersistModelEvent::NAME,
            new PrePersistModelEvent($this->getEnvironment(), $this->model, $this->originalModel)
        );
    }

    /**
     * Trigger the post persist event and handle the postPersist function if available.
     *
     * @return void
     */
    protected function handlePostPersist()
    {
        if ($this->postFunction != null) {
            call_user_func($this->postFunction, $this->getEnvironment(), $this->model, $this->originalModel);
        }

        $event = new PostPersistModelEvent($this->getEnvironment(), $this->model, $this->originalModel);
        $this->getEnvironment()->getEventDispatcher()->dispatch($event::NAME, $event);
    }

    /**
     * Get the label for a button from the translator.
     *
     * The fallback is as follows:
     * 1. Try to translate the button via the data definition name as translation section.
     * 2. Try to translate the button name with the prefix 'MSC.'.
     * 3. Return the input value as nothing worked out.
     *
     * @param string $buttonLabel The non translated label for the button.
     *
     * @return string
     */
    protected function getButtonLabel($buttonLabel)
    {
        $translator = $this->getEnvironment()->getTranslator();
        $definition = $this->getDataDefinition();
        if (($label = $translator->translate($buttonLabel, $definition->getName())) !== $buttonLabel) {
            return $label;
        } elseif (($label = $translator->translate('MSC.' . $buttonLabel)) !== $buttonLabel) {
            return $label;
        }

        // Fallback, just return the key as is it.
        return $buttonLabel;
    }

    /**
     * Retrieve a list of html buttons to use in the bottom panel (submit area).
     *
     * @return string[]
     */
    protected function getEditButtons()
    {
        $buttons         = array();
        $definition      = $this->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();

        $buttons['save'] = sprintf(
            '<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="%s" />',
            $this->getButtonLabel('save')
        );

        $buttons['saveNclose'] = sprintf(
            '<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="%s" />',
            $this->getButtonLabel('saveNclose')
        );

        if (!$this->isPopup() && $basicDefinition->isCreatable()) {
            $buttons['saveNcreate'] = sprintf(
                '<input type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n" ' .
                ' value="%s" />',
                $this->getButtonLabel('saveNcreate')
            );
        }

        if ($this->getEnvironment()->getInputProvider()->hasParameter('s2e')) {
            $buttons['saveNedit'] = sprintf(
                '<input type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e" value="%s" />',
                $this->getButtonLabel('saveNedit')
            );
        } elseif (!$this->isPopup()
                  && (($basicDefinition->getMode() == BasicDefinitionInterface::MODE_PARENTEDLIST)
                      || '' !== $basicDefinition->getParentDataProvider()
                      || $basicDefinition->isSwitchToEditEnabled()
                  )
        ) {
            $buttons['saveNback'] = sprintf(
                '<input type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g" value="%s" />',
                $this->getButtonLabel('saveNback')
            );
        }

        $event = new GetEditModeButtonsEvent($this->getEnvironment());
        $event->setButtons($buttons);

        $this->getEnvironment()->getEventDispatcher()->dispatch($event::NAME, $event);

        return $event->getButtons();
    }

    /**
     * Build the field sets.
     *
     * @param ContaoWidgetManager $widgetManager  The widget manager in use.
     *
     * @param PaletteInterface    $palette        The palette to use.
     *
     * @param PropertyValueBag    $propertyValues The property values.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function buildFieldSet($widgetManager, $palette, $propertyValues)
    {
        $environment         = $this->getEnvironment();
        $definition          = $this->getDataDefinition();
        $translator          = $environment->getTranslator();
        $propertyDefinitions = $definition->getPropertiesDefinition();
        $isAutoSubmit        = ($environment->getInputProvider()->getValue('SUBMIT_TYPE') === 'auto');
        $legendStates        = $this->getLegendStates();
        $editInformation     = $GLOBALS['container']['dc-general.edit-information'];

        $fieldSets = array();
        $first     = true;
        foreach ($palette->getLegends() as $legend) {
            $legendName = $translator->translate(
                $legend->getName() . '_legend',
                $definition->getName()
            );
            $fields     = array();
            $properties = $legend->getProperties($this->model, $propertyValues);

            if (!$properties) {
                continue;
            }

            $legendVisible = $this->isLegendVisible($legend, $legendStates);

            foreach ($properties as $property) {
                $this->ensurePropertyExists($property->getName(), $propertyDefinitions);

                // If this property is invalid, fetch the error.
                if ((!$isAutoSubmit)
                    && $propertyValues
                    && $propertyValues->hasPropertyValue($property->getName())
                    && $propertyValues->isPropertyValueInvalid($property->getName())
                ) {
                    // Force legend open on error.
                    $legendVisible = true;

                    $editInformation->setModelError(
                        $this->model,
                        $propertyValues->getPropertyValueErrors($property->getName()),
                        $property
                    );
                }

                $fields[] = $widgetManager->renderWidget($property->getName(), $isAutoSubmit, $propertyValues);
            }

            $fieldSet['label']   = $legendName;
            $fieldSet['class']   = $this->getLegendClass($first, $legendVisible);
            $fieldSet['palette'] = implode('', $fields);
            $fieldSet['legend']  = $legend->getName();
            $fieldSets[]         = $fieldSet;

            $first = false;
        }

        return $fieldSets;
    }

    /**
     * Update the versioning information in the data provider for a given model (if necessary).
     *
     * @param ModelInterface $model The model to update.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function storeVersion(ModelInterface $model)
    {
        $modelId                 = $model->getId();
        $environment             = $this->getEnvironment();
        $definition              = $this->getDataDefinition();
        $dataProvider            = $environment->getDataProvider($model->getProviderName());
        $dataProviderDefinition  = $definition->getDataProviderDefinition();
        $dataProviderInformation = $dataProviderDefinition->getInformation($model->getProviderName());

        if (!$dataProviderInformation->isVersioningEnabled()) {
            return;
        }

        // Compare version and current record.
        $currentVersion = $dataProvider->getActiveVersion($modelId);
        if (!$currentVersion
            || !$dataProvider->sameModels($model, $dataProvider->getVersion($modelId, $currentVersion))
        ) {
            $user = BackendUser::getInstance();

            $dataProvider->saveVersion($model, $user->username);
        }
    }

    /**
     * Retrieve the manual sorting property if any is defined.
     *
     * @return string|null
     */
    protected function getManualSortingProperty()
    {
        return ViewHelpers::getManualSortingProperty($this->getEnvironment());
    }

    /**
     * Handle the submit and determine which button has been triggered.
     *
     * This method will redirect the client.
     *
     * @param ModelInterface $model The model that has been submitted.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function handleSubmit(ModelInterface $model)
    {
        $environment   = $this->getEnvironment();
        $dispatcher    = $environment->getEventDispatcher();
        $inputProvider = $environment->getInputProvider();

        if ($inputProvider->hasValue('save')) {
            $newUrlEvent = new AddToUrlEvent('act=edit&id=' . ModelId::fromModel($model)->getSerialized());
            $dispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $newUrlEvent);
            $dispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, new RedirectEvent($newUrlEvent->getUrl()));
        } elseif ($inputProvider->hasValue('saveNclose')) {
            $this->clearBackendStates();

            $newUrlEvent = new GetReferrerEvent();
            $dispatcher->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $newUrlEvent);
            $dispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, new RedirectEvent($newUrlEvent->getReferrerUrl()));
        } elseif ($inputProvider->hasValue('saveNcreate')) {
            $this->clearBackendStates();
            $after = ModelId::fromModel($model);

            $newUrlEvent = new AddToUrlEvent('act=create&id=&after=' . $after->getSerialized());
            $dispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $newUrlEvent);
            $dispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, new RedirectEvent($newUrlEvent->getUrl()));
        } elseif ($inputProvider->hasValue('saveNback')) {
            $this->clearBackendStates();

            $parentProviderName = $environment->getDataDefinition()->getBasicDefinition()->getParentDataProvider();
            $newUrlEvent        = new GetReferrerEvent(false, $parentProviderName);

            $dispatcher->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $newUrlEvent);
            $dispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, new RedirectEvent($newUrlEvent->getReferrerUrl()));
        }
    }

    /**
     * Determine the headline to use.
     *
     * @return string.
     */
    protected function getHeadline()
    {
        $definitionName = $this->getDataDefinition()->getName();
        $translator     = $this->getEnvironment()->getTranslator();

        if ($this->model->getId()) {
            $headline = $translator->translate('editRecord', $definitionName, array($this->model->getId()));

            if ($headline !== 'editRecord') {
                return $headline;
            }
            return $translator->translate('MSC.editRecord', null, array($this->model->getId()));
        }

        $headline = $translator->translate('newRecord', $definitionName, array($this->model->getId()));
        if ($headline !== 'newRecord') {
            return $headline;
        }

        return $translator->translate('MSC.editRecord', null, array(''));
    }

    /**
     * Handle the persisting of the currently loaded model.
     *
     * @return bool True means everything is okay, False error.
     */
    protected function doPersist()
    {
        $environment     = $this->getEnvironment();
        $dataProvider    = $environment->getDataProvider($this->model->getProviderName());
        $inputProvider   = $environment->getInputProvider();
        $editInformation = $GLOBALS['container']['dc-general.edit-information'];

        if (!$this->model->getMeta(ModelInterface::IS_CHANGED)) {
            return true;
        }

        $this->handlePrePersist();

        if (($this->model->getId() === null) && $this->getManualSortingProperty()) {
            $models = $dataProvider->getEmptyCollection();
            $models->push($this->model);

            $controller = $environment->getController();

            if ($inputProvider->hasParameter('after')) {
                $after = ModelId::fromSerialized($inputProvider->getParameter('after'));

                $previousDataProvider = $environment->getDataProvider($after->getDataProviderName());
                $previousFetchConfig  = $previousDataProvider->getEmptyConfig();
                $previousFetchConfig->setId($after->getId());
                $previousModel = $previousDataProvider->fetch($previousFetchConfig);

                if ($previousModel) {
                    $controller->pasteAfter($previousModel, $models, $this->getManualSortingProperty());
                } else {
                    $controller->pasteTop($models, $this->getManualSortingProperty());
                }
            } elseif ($inputProvider->hasParameter('into')) {
                $into = ModelId::fromSerialized($inputProvider->getParameter('into'));

                $parentDataProvider = $environment->getDataProvider($into->getDataProviderName());
                $parentFetchConfig  = $parentDataProvider->getEmptyConfig();
                $parentFetchConfig->setId($into->getId());
                $parentModel = $parentDataProvider->fetch($parentFetchConfig);

                if ($parentModel) {
                    $controller->pasteInto($parentModel, $models, $this->getManualSortingProperty());
                } else {
                    $controller->pasteTop($models, $this->getManualSortingProperty());
                }
            } else {
                $controller->pasteTop($models, $this->getManualSortingProperty());
            }

            $environment->getClipboard()->clear()->saveTo($environment);
        } else {
            if (!$this->allValuesUnique()) {
                return false;
            }

            // Save the model.
            $dataProvider->save($this->model, $editInformation->uniformTime());
        }

        $this->handlePostPersist();

        $this->storeVersion($this->model);

        return true;
    }

    /**
     * Check if all values are unique, but only for the fields which have the option enabled.
     *
     * @return bool True => everything is okay | False => One value is not unique.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function allValuesUnique()
    {
        // Init some vars.
        $translator      = $this->getEnvironment()->getTranslator();
        $dataProvider    = $this->getEnvironment()->getDataProvider($this->model->getProviderName());
        $propertyNames   = $this->getDataDefinition()->getPropertiesDefinition()->getPropertyNames();
        $editInformation = $GLOBALS['container']['dc-general.edit-information'];

        // Run each and check the unique flag.
        foreach ($propertyNames as $propertyName) {
            $definition = $this->getDataDefinition()->getPropertiesDefinition()->getProperty($propertyName);
            $extra      = $definition->getExtra();
            $value      = $this->model->getProperty($propertyName);

            // Check the flag and the value.
            if (isset($extra['unique']) && $extra['unique'] && $value != '') {
                // Check the database. If return true the value is already in the database.
                if (!$dataProvider->isUniqueValue($propertyName, $value, $this->model->getId())) {
                    $editInformation->setModelError(
                        $this->model,
                        array($translator->translate('not_unique', 'MSC', array($propertyName))),
                        $definition
                    );

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Create the edit mask.
     *
     * @return string
     *
     * @throws DcGeneralRuntimeException         If the data container is not editable, closed.
     *
     * @throws DcGeneralInvalidArgumentException If an unknown property is encountered in the palette.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function execute()
    {
        $environment             = $this->getEnvironment();
        $definition              = $this->getDataDefinition();
        $dataProvider            = $environment->getDataProvider($this->model->getProviderName());
        $dataProviderDefinition  = $definition->getDataProviderDefinition();
        $dataProviderInformation = $dataProviderDefinition->getInformation($this->model->getProviderName());
        $inputProvider           = $environment->getInputProvider();
        $palettesDefinition      = $definition->getPalettesDefinition();
        $blnSubmitted            = ($inputProvider->getValue('FORM_SUBMIT') === $definition->getName());
        $blnIsAutoSubmit         = ($inputProvider->getValue('SUBMIT_TYPE') === 'auto');
        $editInformation         = $GLOBALS['container']['dc-general.edit-information'];

        $widgetManager = new ContaoWidgetManager($environment, $this->model);

        $this->checkEditable($this->model);
        $this->checkCreatable($this->model);

        $environment->getEventDispatcher()->dispatch(
            PreEditModelEvent::NAME,
            new PreEditModelEvent($environment, $this->model)
        );

        $environment->getEventDispatcher()->dispatch(
            DcGeneralEvents::ENFORCE_MODEL_RELATIONSHIP,
            new EnforceModelRelationshipEvent($this->getEnvironment(), $this->model)
        );

        // Pass 1: Get the palette for the values stored in the model.
        $palette = $palettesDefinition->findPalette($this->model);

        $propertyValues = $this->processInput($widgetManager);
        if ($blnSubmitted && $propertyValues) {
            // Pass 2: Determine the real palette we want to work on if we have some data submitted.
            $palette = $palettesDefinition->findPalette($this->model, $propertyValues);

            // Update the model - the model might add some more errors to the propertyValueBag via exceptions.
            $this->getEnvironment()->getController()->updateModelFromPropertyBag($this->model, $propertyValues);
        }

        $fieldSets = $this->buildFieldSet($widgetManager, $palette, $propertyValues);

        if ((!$blnIsAutoSubmit) && $blnSubmitted && !$editInformation->getModelError($this->model)) {
            if ($this->doPersist()) {
                $this->handleSubmit($this->model);
            }
        }

        $objTemplate = new ContaoBackendViewTemplate('dcbe_general_edit');
        $objTemplate->setData(
            array(
                'fieldsets'   => $fieldSets,
                'versions'    => $dataProviderInformation->isVersioningEnabled() ? $dataProvider->getVersions(
                    $this->model->getId()
                ) : null,
                'subHeadline' => $this->getHeadline(),
                'table'       => $definition->getName(),
                'enctype'     => 'multipart/form-data',
                'error'       => $editInformation->getFlatModelErrors($this->model),
                'editButtons' => $this->getEditButtons(),
                'noReload'    => $editInformation->hasAnyModelError(),
                'breadcrumb'  => $this->breadcrumb
            )
        );

        if (in_array(
            'ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface',
            class_implements(
                $environment->getDataProvider(
                    $this->model->getProviderName()
                )
            )
        )) {
            /** @var MultiLanguageDataProviderInterface $dataProvider */
            $langsNative = array();
            require TL_ROOT . '/system/config/languages.php';

            $objTemplate->set(
                'languages',
                $environment->getController()->getSupportedLanguages($this->model->getId())
            )
                ->set('language', $dataProvider->getCurrentLanguage())
                ->set('languageHeadline', $langsNative[$dataProvider->getCurrentLanguage()]);
        } else {
            $objTemplate
                ->set('languages', null)
                ->set('languageHeadline', '');
        }

        return $objTemplate->parse();
    }

    /**
     * Clear the backend messages and offset states.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function clearBackendStates()
    {
        setcookie('BE_PAGE_OFFSET', 0, 0, '/');

        $_SESSION['TL_INFO']    = array();
        $_SESSION['TL_ERROR']   = array();
        $_SESSION['TL_CONFIRM'] = array();
    }

    /**
     * Determine if the passed legend is visible or collapsed.
     *
     * @param LegendInterface $legend       The legend.
     *
     * @param bool[]          $legendStates The states from the session.
     *
     * @return bool
     */
    private function isLegendVisible($legend, $legendStates)
    {
        if (array_key_exists($legend->getName(), $legendStates)) {
            return $legendStates[$legend->getName()];
        }

        return $legend->isInitialVisible();
    }

    /**
     * Obtain the legend states.
     *
     * @return array
     */
    private function getLegendStates()
    {
        $environment  = $this->getEnvironment();
        $definition   = $environment->getDataDefinition();
        $legendStates = $environment->getSessionStorage()->get('LEGENDS') ?: array();

        if (array_key_exists($definition->getName(), $legendStates)) {
            $legendStates = $legendStates[$definition->getName()];

            return $legendStates;
        } else {
            $legendStates = array();

            return $legendStates;
        }
    }

    /**
     * Determine the class to use for a legend.
     *
     * @param bool $first   Flag if this is the first legend.
     *
     * @param bool $visible Flag determining if the legend is visible.
     *
     * @return string
     */
    private function getLegendClass($first, $visible)
    {
        $classes = array($first ? 'tl_tbox' : 'tl_box');

        if (!$visible) {
            $classes[] = ' collapsed';
        }

        return implode(' ', $classes);
    }
}
