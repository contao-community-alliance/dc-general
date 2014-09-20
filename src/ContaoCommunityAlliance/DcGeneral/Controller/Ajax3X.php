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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;

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
    protected function loadPagetree(DataContainerInterface $objDc)
    {
        $environment = $objDc->getEnvironment();
        $input       = $environment->getInputProvider();
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

        $nodes          = $input->getPersistentValue($ajaxKey);
        $nodes[$ajaxId] = intval($input->getValue('state'));
        $input->setPersistentValue($ajaxKey, $nodes);

        $arrData['strTable'] = $environment->getDataDefinition()->getName();
        $arrData['id']       = $ajaxName ?: $rootId;
        $arrData['name']     = $name;

        /** @var \PageSelector $objWidget */
        $objWidget        = new $GLOBALS['BE_FFL']['pageSelector']($arrData, $objDc);
        $objWidget->value = $this->getTreeValue('page', $input->getValue('value'));

        echo $objWidget->generateAjax($ajaxId, $field, $level);
        exit;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function loadFiletree(DataContainerInterface $objDc)
    {
        $environment = $objDc->getEnvironment();
        $input       = $environment->getInputProvider();
        $folder      = $input->getValue('folder');
        $field       = $input->getParameter('field');
        $level       = intval($input->getValue('level'));

        $arrData['strTable'] = $input->getParameter('table');
        $arrData['id']       = $field;
        $arrData['name']     = $field;

        /** @var \FileSelector $objWidget */
        $objWidget = new $GLOBALS['BE_FFL']['fileSelector']($arrData, $objDc);

        $objWidget->value = $this->getTreeValue($field, $input->getValue('value'));
        // Load a particular node.
        if ($folder != '') {
            echo $objWidget->generateAjax($folder, $field, $level);
        } else {
            echo $objWidget->generate();
        }
        exit;
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
     * Reload the file tree.
     *
     * @param string                 $strType The type.
     *
     * @param DataContainerInterface $objDc   The data container.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function reloadTree($strType, DataContainerInterface $objDc)
    {
        $environment  = $objDc->getEnvironment();
        $input        = $environment->getInputProvider();
        $serializedId = $input->hasParameter('id') ? $input->getParameter('id') : null;
        $fieldName    = $input->hasValue('name') ? $input->getValue('name') : null;

        // Handle the keys in "edit multiple" mode.
        if (self::getGet('act') == 'editAll') {
            // TODO: change here when implementing editAll.
            $serializedId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $fieldName);
            // $field        = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $fieldName);
        }

        if ($serializedId !== null) {
            $modelId      = IdSerializer::fromSerialized($serializedId);
            $dataProvider = $objDc->getEnvironment()->getDataProvider($modelId->getDataProviderName());
            $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

            if ($model === null) {
                $this->log(
                    'A record with the ID "' . $serializedId . '" does not exist in "' .
                    $objDc->getEnvironment()->getDataDefinition()->getName() . '"',
                    'Ajax executePostActions()',
                    TL_ERROR
                );
                header('HTTP/1.1 400 Bad Request');
                die('Bad Request');
            }
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
        $arrAttribs['strTable'] = $objDc->getEnvironment()->getDataDefinition()->getName();
        $arrAttribs['strField'] = $fieldName;

        /** @var \Widget $objWidget */
        $objWidget = new $GLOBALS['BE_FFL'][$strKey]($arrAttribs);
        echo $objWidget->generate();

        exit;
    }

    /**
     * {@inheritDoc}
     */
    protected function reloadPagetree(DataContainerInterface $objDc)
    {
        $this->reloadTree('page', $objDc);
    }

    /**
     * {@inheritDoc}
     */
    protected function reloadFiletree(DataContainerInterface $objDc)
    {
        $this->reloadTree('file', $objDc);
    }
}
