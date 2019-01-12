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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use Contao\Ajax as ContaoAjax;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Ajax - General purpose Ajax handler for "executePostActions" as we can not use the default Contao
 * handling.
 *
 * See Contao core issue #5957. https://github.com/contao/core/pull/5957
 */
abstract class Ajax implements EnvironmentAwareInterface
{
    /**
     * The data container calling us.
     *
     * @var DataContainerInterface
     */
    protected $objDc;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        // DO NOT! call parent::__construct(); as otherwise we will end up having references in this class.
    }

    /**
     * Get the data container.
     *
     * @return DataContainerInterface.
     */
    protected function getDataContainer()
    {
        return $this->objDc;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->getDataContainer()->getEnvironment();
    }

    /**
     * Compat wrapper for contao 2.X and 3.X - delegates to the relevant input handler.
     *
     * @param string $key               The key to retrieve.
     * @param bool   $blnDecodeEntities Decode the entities.
     *
     * @return mixed
     */
    protected function getGet($key, $blnDecodeEntities = false)
    {
        return $this->getEnvironment()->getInputProvider()->getParameter($key, $blnDecodeEntities);
    }

    /**
     * Compatibility wrapper for contao 2.X and 3.X - delegates to the relevant input handler.
     *
     * @param string $key               The key to retrieve.
     * @param bool   $blnDecodeEntities Decode the entities.
     *
     * @return mixed
     */
    protected function getPost($key, $blnDecodeEntities = false)
    {
        return $this->getEnvironment()->getInputProvider()->getValue($key, $blnDecodeEntities);
    }

    /**
     * Retrieve the ajax id.
     *
     * @return string
     *
     * @deprecated
     */
    protected function getAjaxId()
    {
        return preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $this->getPost('id'));
    }

    /**
     * Load a tree structure.
     *
     * This method exits the script!
     *
     * @return void
     */
    protected function loadStructure()
    {
        // Method ajaxTreeView is in TreeView.php - watch out!
        $response = new Response(
            $this->getDataContainer()->ajaxTreeView($this->getAjaxId(), (int) $this->getPost('level'))
        );

        throw new ResponseException($response);
    }

    /**
     * Load a file manager tree structure.
     *
     * This method exits the script!
     *
     * @return void
     */
    protected function loadFileManager()
    {
        // Method ajaxTreeView is in TreeView.php - watch out!
        $response = new Response(
            $this->getDataContainer()->ajaxTreeView($this->getPost('folder', true), (int) $this->getPost('level'))
        );

        throw new ResponseException($response);
    }

    /**
     * Load the page tree.
     *
     * @return mixed
     */
    abstract protected function loadPagetree();

    /**
     * Load the file tree.
     *
     * @return mixed
     */
    abstract protected function loadFiletree();

    /**
     * Reload a page tree.
     *
     * This method exits the script.
     *
     * @return void
     */
    abstract protected function reloadPagetree();

    /**
     * Reload a file tree.
     *
     * This method exits the script.
     *
     * @return void
     */
    abstract protected function reloadFiletree();

    /**
     * Toggle a legend.
     *
     * This method exits the script.
     *
     * @return void
     */
    abstract protected function setLegendState();

    /**
     * Handle the post actions from DcGeneral.
     *
     * @param DataContainerInterface $objDc The data container.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function executePostActions(DataContainerInterface $objDc)
    {
        \header('Content-Type: text/html; charset=' . $GLOBALS['TL_CONFIG']['characterSet']);

        $this->objDc = $objDc;

        $action = $this->getEnvironment()->getInputProvider()->getValue('action');

        if (\in_array(
            $action,
            [
                // This is impossible to handle generically in DcGeneral.
                'toggleFeatured',
                // DcGeneral handles sub palettes differently.
                'toggleSubpalette'
            ]
        )) {
            return;
        }

        if (\in_array(
            $action,
            [
                // Load nodes of the page structure tree. Compatible between 2.X and 3.X.
                'loadStructure',
                // Load nodes of the file manager tree.
                'loadFileManager',
                // Load nodes of the page tree.
                'loadPagetree',
                // Load nodes of the file tree.
                'loadFiletree',
                // Reload the page/file picker.
                'reloadPagetree',
                'reloadFiletree',
                'setLegendState'
            ]
        )) {
            $this->{$action}();
            return;
        }

        $ajax = new ContaoAjax($action);
        $ajax->executePreActions();
        $ajax->executePostActions(new DcCompat($this->getEnvironment(), $this->getActiveModel()));
    }

    /**
     * Get the active model.
     *
     * @return \ContaoCommunityAlliance\DcGeneral\Data\ModelInterface|null
     */
    private function getActiveModel()
    {
        $input = $this->getEnvironment()->getInputProvider();
        if (false === $input->hasParameter('id')) {
            return null;
        }

        $modelId      = ModelId::fromSerialized($input->getParameter('id'));
        $dataProvider = $this->getEnvironment()->getDataProvider($modelId->getDataProviderName());

        $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        return $model;
    }

    /**
     * Convenience method to exit the script.
     *
     * Will get called from subclasses to have a central endpoint to exit the script.
     *
     * @return void
     *
     * @deprecated Deperecated since 2.1 and where remove in 3.0. Use own response exit.
     *
     * @SuppressWarnings(PHPMD.ExitExpression) - The whole purpose of the method is the exit expression.
     */
    protected function exitScript()
    {
        // @codingStandardsIgnoreStart
        @\trigger_error('Use own response exit!', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        $session = System::getContainer()->get('session');
        $sessionBag = $session->getBag('contao_backend')->all();

        $user = BackendUser::getInstance();

        Database::getInstance()->prepare('UPDATE tl_user SET tl_user.session=? WHERE tl_user.id=?')
            ->execute(\serialize($sessionBag), $user->id);

        exit;
    }
}
