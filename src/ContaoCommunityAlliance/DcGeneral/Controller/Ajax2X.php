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

use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class GeneralAjax - General purpose Ajax handler for "executePostActions" in Contao 2.X as we can not use the default
 * Contao handling.
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 */
class Ajax2X extends Ajax
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
        $objWidget = new $GLOBALS['BE_FFL']['pageTree']($arrData, $objDc);
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
        $table               = $objDc->getEnvironment()->getDataDefinition()->getName();
        $arrData['strTable'] = $table;
        $arrData['id']       = self::getAjaxName() ?: $objDc->getId();
        $arrData['name']     = self::getPost('name');

        /** @var \FileTree $objWidget */
        $objWidget = new $GLOBALS['BE_FFL']['fileTree']($arrData, $objDc);

        // Load a particular node.
        if (self::getPost('folder', true) != '') {
            echo $objWidget->generateAjax(
                self::getPost('folder', true),
                self::getPost('field'),
                intval(self::getPost('level'))
            );
        } else {
            // Reload the whole tree.
            $user    = \BackendUser::getInstance();
            $strTree = '';
            $path    = $GLOBALS['TL_DCA'][$table]['fields'][self::getPost('field')]['eval']['path'];

            // Set a custom path.
            if (strlen($path)) {
                $strTree = $objWidget->generateAjax(
                    $path,
                    self::getPost('field'),
                    intval(self::getPost('level'))
                );
            } elseif ($user->isAdmin) {
                // Start from root.
                $strTree = $objWidget->generateAjax(
                    $GLOBALS['TL_CONFIG']['uploadPath'],
                    self::getPost('field'),
                    intval(self::getPost('level'))
                );
            } else {
                // Set file mounts.
                foreach ($this->eliminateNestedPaths($this->User->filemounts) as $node) {
                    $strTree .= $objWidget->generateAjax(
                        $node,
                        self::getPost('field'),
                        intval(self::getPost('level')),
                        true
                    );
                }
            }

            echo $strTree;
        }
        exit;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException as it is only present in Contao 3.X.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function reloadPagetree(DataContainerInterface $objDc)
    {
        throw new DcGeneralRuntimeException('Contao 3.X only.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException as it is only present in Contao 3.X.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function reloadFiletree(DataContainerInterface $objDc)
    {
        throw new DcGeneralRuntimeException('Contao 3.X only.');
    }
}
