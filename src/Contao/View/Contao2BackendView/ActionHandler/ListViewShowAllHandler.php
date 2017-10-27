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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This class handles the rendering of list view "showAll" actions.
 */
class ListViewShowAllHandler extends AbstractListShowAllHandler
{
    /**
     * {@inheritDoc}
     */
    protected function wantToHandle($mode)
    {
        return BasicDefinitionInterface::MODE_FLAT === $mode;
    }

    /**
     * {@inheritDoc}
     */
    protected function determineTemplate($groupingInformation)
    {
        if (isset($groupingInformation['mode'])
            && ($groupingInformation['mode'] != GroupAndSortingInformationInterface::GROUP_NONE)
        ) {
            return $this->getTemplate('dcbe_general_grouping');
        }

        if (isset($groupingInformation['property']) && ($groupingInformation['property'] != '')) {
            return $this->getTemplate('dcbe_general_listView_sorting');
        }

        return $this->getTemplate('dcbe_general_listView');
    }

    /**
     * {@inheritdoc}
     */
    protected function renderTemplate(ContaoBackendViewTemplate $template, EnvironmentInterface $environment)
    {
        $dataDefinition            = $environment->getDataDefinition();
        $viewDefinition            = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $groupAndSortingDefinition = $viewDefinition->getListingConfig()->getGroupAndSortingDefinition();

        $pasteButton = $this->renderPasteTopButton($environment, $groupAndSortingDefinition);

        parent::renderTemplate($template, $environment);
        $template->set('header', $pasteButton ? $this->getEmptyHeader() : null);
        $template->set('headerButtons', $this->renderPasteTopButton($environment, $groupAndSortingDefinition));
    }

    /**
     * Get the empty header.
     *
     * @return array
     */
    private function getEmptyHeader()
    {
        return array('' => '');
    }
}
