<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;

/**
 * Class GeneralAjax - General purpose Ajax handler for "executePostActions" in Contao 3.X as we can not use the default
 * Contao handling.
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 */
class Ajax3X extends Ajax
{
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
        $field       = $input->getParameter('field');
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
     * @return string The values as serialized array.
     */
    protected function getTreeValue($strType, $varValue)
    {
        // Convert the selected values.
        if ($varValue != '') {
            $varValue = trimsplit("\t", $varValue);

            // Automatically add resources to the DBAFS.
            if ($strType == 'file') {
                foreach ($varValue as $k => $v) {
                    if (version_compare(VERSION, '3.2', '<')) {
                        $varValue[$k] = \Dbafs::addResource($v)->id;
                    } else {
                        $varValue[$k] = \Dbafs::addResource($v)->uuid;
                    }
                }
            }

            $varValue = serialize($varValue);
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
        $modelId      = IdSerializer::fromSerialized($serializedId);
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
     * @param string $strType The type.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function reloadTree($strType)
    {
        $environment  = $this->getEnvironment();
        $input        = $environment->getInputProvider();
        $serializedId = $input->hasParameter('id') ? $input->getParameter('id') : null;
        $fieldName    = $input->hasValue('name') ? $input->getValue('name') : null;

        if ($serializedId !== null) {
            $model = $this->getModelFromSerializedId($serializedId);
        }

        $varValue = $this->getTreeValue($strType, $input->getValue('value'));
        $strKey   = $strType . 'Tree';

        // Set the new value.
        if (isset($model)) {
            $model->setProperty($fieldName, $varValue);
            $arrAttribs['activeRecord'] = $model;
        } else {
            $arrAttribs['activeRecord'] = null;
        }

        $arrAttribs['id']       = $fieldName;
        $arrAttribs['name']     = $fieldName;
        $arrAttribs['value']    = $varValue;
        $arrAttribs['strTable'] = $environment->getDataDefinition()->getName();
        $arrAttribs['strField'] = $fieldName;

        /** @var \Widget $objWidget */
        $objWidget = new $GLOBALS['BE_FFL'][$strKey]($arrAttribs);
        echo $objWidget->generate();

        $this->exitScript();
    }

    /**
     * {@inheritDoc}
     */
    protected function reloadPagetree()
    {
        $this->reloadTree('page');
    }

    /**
     * {@inheritDoc}
     */
    protected function reloadFiletree()
    {
        $this->reloadTree('file');
    }
}
