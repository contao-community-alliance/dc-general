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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\ElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\FilterElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\LimitElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SearchElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SortElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SubmitElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultFilterElement;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultLimitElement;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultPanel;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultPanelContainer;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultSearchElement;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultSortElement;
use ContaoCommunityAlliance\DcGeneral\Panel\DefaultSubmitElement;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelElementInterface;

/**
 * This class builds a panel for an environment.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PanelBuilder
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    private EnvironmentInterface $environment;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment.
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Build the panel.
     *
     * @return DefaultPanelContainer
     */
    public function build()
    {
        $panel = new DefaultPanelContainer();
        $panel->setEnvironment($this->environment);

        $definition = $this->environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $viewDefinition = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        assert($viewDefinition instanceof Contao2BackendViewDefinitionInterface);

        foreach ($viewDefinition->getPanelLayout()->getRows() as $panelKey => $row) {
            $panelRow = new DefaultPanel();
            $panel->addPanel((string) $panelKey, $panelRow);
            foreach ($row as $element) {
                /** @var ElementInformationInterface $element */
                if (null !== $instance = $this->createElement($element)) {
                    $panelRow->addElement($element->getName(), $instance);
                }
            }
        }

        return $panel;
    }

    /**
     * Create the panel element.
     *
     * @param ElementInformationInterface $element The element being created.
     *
     * @return PanelElementInterface|null
     */
    private function createElement($element)
    {
        if ($element instanceof FilterElementInformationInterface) {
            $panelElement = new DefaultFilterElement();
            return $panelElement->setPropertyName($element->getPropertyName());
        }
        if ($element instanceof LimitElementInformationInterface) {
            return new DefaultLimitElement();
        }
        if ($element instanceof SearchElementInformationInterface) {
            return $this->buildSearchElement($element);
        }
        if ($element instanceof SortElementInformationInterface) {
            return new DefaultSortElement();
        }
        if ($element instanceof SubmitElementInformationInterface) {
            return new DefaultSubmitElement();
        }

        return null;
    }

    /**
     * Build a search element.
     *
     * @param SearchElementInformationInterface $element The element definition.
     *
     * @return DefaultSearchElement
     */
    private function buildSearchElement($element)
    {
        $panelElement = new DefaultSearchElement();
        foreach ($element->getPropertyNames() as $propName) {
            $panelElement->addProperty($propName);
        }

        return $panelElement;
    }
}
