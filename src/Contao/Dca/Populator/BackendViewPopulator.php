<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BackendViewInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ListView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\PanelBuilder;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ParentView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreeView;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * This class is the default fallback populator in the Contao Backend to instantiate a BackendView.
 *
 * @psalm-suppress PropertyNotSetInConstructor - can not make setScopeDeterminator() final without major release.
 */
class BackendViewPopulator extends AbstractEventDrivenBackendEnvironmentPopulator
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * The token manager.
     *
     * @var CsrfTokenManagerInterface
     */
    private CsrfTokenManagerInterface $tokenManager;

    /**
     * The token name.
     *
     * @var string
     */
    private string $tokenName;

    /**
     * BackendViewPopulator constructor.
     *
     * @param RequestScopeDeterminator       $scopeDeterminator The request mode determinator.
     * @param CsrfTokenManagerInterface|null $tokenManager      The token manager.
     * @param string|null                    $tokenName         The token name.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        ?CsrfTokenManagerInterface $tokenManager = null,
        ?string $tokenName = null
    ) {
        if (null === $tokenManager) {
            $tokenManager = System::getContainer()->get('security.csrf.token_manager');
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the csrf token manager as 2th argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in DCG 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }
        if (null === $tokenName) {
            $tokenName = System::getContainer()->getParameter('contao.csrf_token_name');
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the csrf token name as 3th argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in DCG 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        $this->tokenManager = $tokenManager;
        $this->tokenName    = $tokenName;

        $this->setScopeDeterminator($scopeDeterminator);
    }

    /**
     * Create a view instance in the environment if none has been defined yet.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException Upon an unknown view mode has been encountered.
     *
     * @internal
     */
    protected function populateView(EnvironmentInterface $environment)
    {
        // Already populated? Get out then.
        if ($environment->getView()) {
            return;
        }

        $dataDefinition = $environment->getDataDefinition();

        if (!$dataDefinition || !$dataDefinition->hasBasicDefinition()) {
            return;
        }

        switch ($dataDefinition->getBasicDefinition()->getMode()) {
            case BasicDefinitionInterface::MODE_FLAT:
                $view = new ListView($this->scopeDeterminator);
                break;
            case BasicDefinitionInterface::MODE_PARENTEDLIST:
                $view = new ParentView($this->scopeDeterminator);
                break;
            case BasicDefinitionInterface::MODE_HIERARCHICAL:
                $view = new TreeView($this->scopeDeterminator, $this->tokenManager, $this->tokenName);
                break;
            default:
                $mode = $dataDefinition->getBasicDefinition()->getMode();
                throw new DcGeneralInvalidArgumentException(
                    'Unknown view mode encountered: ' . var_export($mode, true)
                );
        }

        $view->setEnvironment($environment);
        $environment->setView($view);
    }

    /**
     * Create a panel instance in the view if none has been defined yet.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     *
     * @internal
     */
    protected function populatePanel(EnvironmentInterface $environment)
    {
        /** @var BackendViewInterface $view */
        $view = $environment->getView();

        // Already populated or not in Backend? Get out then.
        if (!(($view instanceof BaseView))) {
            return;
        }

        $definition = $environment->getDataDefinition();
        if (!$definition || !$definition->hasDefinition(Contao2BackendViewDefinitionInterface::NAME)) {
            return;
        }

        // Already populated.
        if ($view->getPanel()) {
            return;
        }

        $view->setPanel((new PanelBuilder($environment))->build());
    }

    /**
     * Create a view instance in the environment if none has been defined yet.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     */
    public function populate(EnvironmentInterface $environment)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $this->populateView($environment);
        $this->populatePanel($environment);
    }
}
