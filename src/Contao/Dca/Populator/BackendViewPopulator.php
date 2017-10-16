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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

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

/**
 * This class is the default fallback populator in the Contao Backend to instantiate a BackendView.
 */
class BackendViewPopulator extends AbstractEventDrivenBackendEnvironmentPopulator
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * BackendViewPopulator constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
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

        $definition = $environment->getDataDefinition();

        if (!$definition->hasBasicDefinition()) {
            return;
        }

        $definition = $definition->getBasicDefinition();

        switch ($definition->getMode()) {
            case BasicDefinitionInterface::MODE_FLAT:
                $view = new ListView($this->scopeDeterminator);
                break;
            case BasicDefinitionInterface::MODE_PARENTEDLIST:
                $view = new ParentView($this->scopeDeterminator);
                break;
            case BasicDefinitionInterface::MODE_HIERARCHICAL:
                $view = new TreeView($this->scopeDeterminator);
                break;
            default:
                throw new DcGeneralInvalidArgumentException('Unknown view mode encountered: ' . $definition->getMode());
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
        // Already populated or not in Backend? Get out then.
        if (!(($environment->getView() instanceof BaseView))) {
            return;
        }

        $definition = $environment->getDataDefinition();

        if (!$definition->hasDefinition(Contao2BackendViewDefinitionInterface::NAME)) {
            return;
        }

        /** @var BackendViewInterface $view */
        $view = $environment->getView();

        // Already populated.
        if ($view->getPanel()) {
            return;
        }

        $builder = new PanelBuilder($environment);
        $view->setPanel($builder->build());
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
