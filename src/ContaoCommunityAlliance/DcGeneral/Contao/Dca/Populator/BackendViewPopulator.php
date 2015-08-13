<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
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
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BackendViewInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ListView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ParentView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreeView;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\FilterElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\LimitElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SearchElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SortElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SubmitElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelLayoutInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultFilterElement;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultLimitElement;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultPanel;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultPanelContainer;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultSearchElement;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultSortElement;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultSubmitElement;

/**
 * This class is the default fallback populator in the Contao Backend to instantiate a BackendView.
 */
class BackendViewPopulator extends AbstractEventDrivenEnvironmentPopulator
{
    const PRIORITY = 100;

    /**
     * Create a view instance in the environment if none has been defined yet.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     *
     * @throws DcGeneralInvalidArgumentException Upon an unknown view mode has been encountered.
     * @internal
     */
    protected function populateView(EnvironmentInterface $environment)
    {
        // Already populated or not in Backend? Get out then.
        if ($environment->getView() || (TL_MODE != 'BE')) {
            return;
        }

        $definition = $environment->getDataDefinition();

        if (!$definition->hasBasicDefinition()) {
            return;
        }

        $definition = $definition->getBasicDefinition();

        switch ($definition->getMode()) {
            case BasicDefinitionInterface::MODE_FLAT:
                $view = new ListView();
                break;
            case BasicDefinitionInterface::MODE_PARENTEDLIST:
                $view = new ParentView();
                break;
            case BasicDefinitionInterface::MODE_HIERARCHICAL:
                $view = new TreeView();
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
        if (!(($environment->getView() instanceof BaseView) && (TL_MODE == 'BE'))) {
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

        $panel = new DefaultPanelContainer();
        $panel->setEnvironment($environment);
        $view->setPanel($panel);

        /** @var Contao2BackendViewDefinitionInterface $viewDefinition */
        $viewDefinition = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);

        /** @var PanelLayoutInterface $panelLayout */
        $panelLayout = $viewDefinition->getPanelLayout();

        foreach ($panelLayout->getRows() as $panelKey => $row) {
            // We need a new panel.
            $panelRow = new DefaultPanel();

            $panel->addPanel($panelKey, $panelRow);

            foreach ($row as $element) {
                if ($element instanceof FilterElementInformationInterface) {
                    $panelElement = new DefaultFilterElement();
                    $panelElement->setPropertyName($element->getPropertyName());
                    $panelRow->addElement($element->getName(), $panelElement);
                } elseif ($element instanceof LimitElementInformationInterface) {
                    $panelElement = new DefaultLimitElement();
                    $panelRow->addElement($element->getName(), $panelElement);
                } elseif ($element instanceof SearchElementInformationInterface) {
                    $panelElement = new DefaultSearchElement();

                    foreach ($element->getPropertyNames() as $propName) {
                        $panelElement->addProperty($propName);
                    }

                    $panelRow->addElement($element->getName(), $panelElement);
                } elseif ($element instanceof SortElementInformationInterface) {
                    $panelElement = new DefaultSortElement();
                    $panelRow->addElement($element->getName(), $panelElement);
                } elseif ($element instanceof SubmitElementInformationInterface) {
                    $panelElement = new DefaultSubmitElement();
                    $panelRow->addElement($element->getName(), $panelElement);
                }
            }
        }
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
        $this->populateView($environment);

        $this->populatePanel($environment);
    }
}
