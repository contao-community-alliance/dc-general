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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;

/**
 * Class GeneralAjax - General purpose Ajax handler for "executePostActions" in Contao 3.X as we can not use the default
 * Contao handling.
 */
class Ajax3X extends Ajax
{
    /**
     * Get the widget instance.
     *
     * @param string $fieldName     The property name.
     *
     * @param string $serializedId  The serialized id of the model.
     *
     * @param string $propertyValue The property value.
     *
     * @return Widget
     */
    protected function getWidget($fieldName, $serializedId, $propertyValue)
    {
        $environment    = $this->getEnvironment();
        $property       = $environment->getDataDefinition()->getPropertiesDefinition()->getProperty($fieldName);
        $propertyValues = new PropertyValueBag();

        if ($serializedId !== null) {
            $model = $this->getModelFromSerializedId($serializedId);
        } else {
            $dataProvider = $environment->getDataProvider();
            $model        = $dataProvider->getEmptyModel();
        }

        $widgetManager = new ContaoWidgetManager($environment, $model);

        // Process input and update changed properties.
        $treeType      = substr($property->getWidgetType(), 0, 4);
        $propertyValue = $this->getTreeValue($treeType, $propertyValue);
        if (($treeType == 'file') || ($treeType == 'page')) {
            $extra = $property->getExtra();
            if (is_array($propertyValue) && !isset($extra['multiple'])) {
                $propertyValue = $propertyValue[0];
            } else {
                $propertyValue = implode(',', (array) $propertyValue);
            }
        }

        $propertyValues->setPropertyValue($fieldName, $propertyValue);
        $widgetManager->processInput($propertyValues);
        $model->setProperty($fieldName, $propertyValues->getPropertyValue($fieldName));

        $widget = $widgetManager->getWidget($fieldName);

        return $widget;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function loadPagetree()
    {
        $environment = $this->getEnvironment();
        $input       = $environment->getInputProvider();
        $session     = $environment->getSessionStorage();
        $field       = $input->getValue('field');
        $name        = $input->getValue('name');
        $level       = intval($input->getValue('level'));
        $rootId      = $input->getValue('id');

        $ajaxId   = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $rootId);
        $ajaxKey  = str_replace('_' . $ajaxId, '', $rootId);
        $ajaxName = null;
        if ($input->getValue('act') == 'editAll') {
            $ajaxKey  = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $ajaxKey);
            $ajaxName = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $name);
        }

        $nodes          = $session->get($ajaxKey);
        $nodes[$ajaxId] = intval($input->getValue('state'));
        $session->set($ajaxKey, $nodes);

        $arrData['strTable'] = $environment->getDataDefinition()->getName();
        $arrData['id']       = $ajaxName ?: $rootId;
        $arrData['name']     = $name;

        /** @var \PageSelector $objWidget */
        $objWidget        = new $GLOBALS['BE_FFL']['pageSelector']($arrData, $this->getDataContainer());
        $objWidget->value = $this->getTreeValue('page', $input->getValue('value'));

        echo $objWidget->generateAjax($ajaxId, $field, $level);

        $this->exitScript();
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function loadFiletree()
    {
        $environment = $this->getEnvironment();
        $input       = $environment->getInputProvider();
        $folder      = $input->getValue('folder');
        $field       = $input->getValue('field');
        $level       = intval($input->getValue('level'));

        $arrData['strTable'] = $input->getParameter('table');
        $arrData['id']       = $field;
        $arrData['name']     = $field;
        $arrData             = array_merge(
            $environment->getDataDefinition()->getPropertiesDefinition()->getProperty($field)->getExtra(),
            $arrData
        );

        /** @var \FileSelector $objWidget */
        $objWidget = new $GLOBALS['BE_FFL']['fileSelector']($arrData, $this->getDataContainer());

        $objWidget->value = $this->getTreeValue($field, $input->getValue('value'));
        // Load a particular node.
        if ($folder != '') {
            echo $objWidget->generateAjax($folder, $field, $level);
        } else {
            echo $objWidget->generate();
        }

        $this->exitScript();
    }

    /**
     * Retrieve the value as serialized array.
     *
     * If the type is "file", the file names will automatically be added to the Dbafs and converted to file id.
     *
     * @param string $strType  Either "page" or "file".
     *
     * @param string $varValue The value as comma separated list.
     *
     * @return string The value array.
     */
    protected function getTreeValue($strType, $varValue)
    {
        // Convert the selected values.
        if ($varValue != '') {
            $varValue = trimsplit("\t", $varValue);

            // Automatically add resources to the DBAFS.
            if ($strType == 'file') {
                foreach ($varValue as $k => $v) {
                    $varValue[$k] = call_user_func('\Contao\StringUtil::binToUuid', \Dbafs::addResource($v)->uuid);
                }
            }
        }

        return $varValue;
    }

    /**
     * Retrieve a model from a serialized id.
     *
     * Exits the script if no model has been found.
     *
     * @param string $serializedId The serialized id.
     *
     * @return ModelInterface
     */
    protected function getModelFromSerializedId($serializedId)
    {
        $modelId      = ModelId::fromSerialized($serializedId);
        $dataProvider = $this->getEnvironment()->getDataProvider($modelId->getDataProviderName());
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        if ($model === null) {
            $event = new LogEvent(
                'A record with the ID "' . $serializedId . '" does not exist in "' .
                $this->getEnvironment()->getDataDefinition()->getName() . '"',
                'Ajax executePostActions()',
                TL_ERROR
            );
            $this->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::SYSTEM_LOG, $event);
            header('HTTP/1.1 400 Bad Request');
            echo 'Bad Request';
            $this->exitScript();
        }

        return $model;
    }

    /**
     * Reload the file tree.
     *
     * @return void
     */
    protected function reloadTree()
    {
        $environment  = $this->getEnvironment();
        $input        = $environment->getInputProvider();
        $serializedId = ($input->hasParameter('id') && $input->getParameter('id')) ? $input->getParameter('id') : null;
        $fieldName    = $input->hasValue('name') ? $input->getValue('name') : null;
        $value        = $input->hasValue('value') ? $input->getValue('value', true) : null;

        $widget = $this->getWidget($fieldName, $serializedId, $value);

        echo $widget->generate();
        $this->exitScript();
    }

    /**
     * {@inheritDoc}
     */
    protected function reloadPagetree()
    {
        $this->reloadTree();
    }

    /**
     * {@inheritDoc}
     */
    protected function reloadFiletree()
    {
        $this->reloadTree();
    }

    /**
     * {@inheritDoc}
     */
    protected function setLegendState()
    {
        $environment = $this->getEnvironment();
        $input       = $environment->getInputProvider();
        $table       = $input->getValue('table');
        $legend      = $input->getValue('legend');
        $state       = (bool) $input->getValue('state');
        $session     = $environment->getSessionStorage();
        $states      = $session->get('LEGENDS');

        $states[$table][$legend] = $state;
        $session->set('LEGENDS', $states);

        $this->exitScript();
    }
}
