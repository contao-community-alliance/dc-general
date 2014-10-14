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
    protected function loadPagetree()
    {
        $environment = $this->getEnvironment();
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
        $objWidget = new $GLOBALS['BE_FFL']['pageTree']($arrData, $this->getDataContainer());
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
        $table               = $this->getEnvironment()->getDataDefinition()->getName();
        $arrData['strTable'] = $table;
        $arrData['id']       = $this->getAjaxName() ?: $this->getDataContainer()->getId();
        $arrData['name']     = $this->getPost('name');

        /** @var \FileTree $objWidget */
        $objWidget = new $GLOBALS['BE_FFL']['fileTree']($arrData, $this->getDataContainer());

        // Load a particular node.
        if ($this->getPost('folder', true) != '') {
            echo $objWidget->generateAjax(
                $this->getPost('folder', true),
                $this->getPost('field'),
                intval($this->getPost('level'))
            );
        } else {
            // Reload the whole tree.
            $user    = \BackendUser::getInstance();
            $strTree = '';
            $path    = $GLOBALS['TL_DCA'][$table]['fields'][$this->getPost('field')]['eval']['path'];

            // Set a custom path.
            if (strlen($path)) {
                $strTree = $objWidget->generateAjax(
                    $path,
                    $this->getPost('field'),
                    intval($this->getPost('level'))
                );
            } elseif ($user->isAdmin) {
                // Start from root.
                $strTree = $objWidget->generateAjax(
                    $GLOBALS['TL_CONFIG']['uploadPath'],
                    $this->getPost('field'),
                    intval($this->getPost('level'))
                );
            } else {
                // Set file mounts.
                foreach ($this->eliminateNestedPaths($this->User->filemounts) as $node) {
                    $strTree .= $objWidget->generateAjax(
                        $node,
                        $this->getPost('field'),
                        intval($this->getPost('level')),
                        true
                    );
                }
            }

            echo $strTree;
        }

        $this->exitScript();
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException as it is only present in Contao 3.X.
     */
    protected function reloadPagetree()
    {
        throw new DcGeneralRuntimeException('Contao 3.X only.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException as it is only present in Contao 3.X.
     */
    protected function reloadFiletree()
    {
        throw new DcGeneralRuntimeException('Contao 3.X only.');
    }
}
