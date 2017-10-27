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

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;

/**
 * Action handler for paste actions.
 */
class PasteHandler
{
    use CallActionTrait;
    use RequestScopeDeterminatorAwareTrait;

    /**
     * PasteHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->setScopeDeterminator($scopeDeterminator);
    }

    /**
     * Handle the event to process the action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        if ($event->getAction()->getName() !== 'paste') {
            return;
        }

        if (false === $this->checkPermission($event)) {
            $event->stopPropagation();

            return;
        }

        $response = $this->process($event->getEnvironment());
        $event->setResponse($response);
    }

    /**
     * Handle the action.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    protected function process(EnvironmentInterface $environment)
    {
        $input       = $environment->getInputProvider();
        $clipboard   = $environment->getClipboard();
        $definition  = $environment->getDataDefinition()->getBasicDefinition();

        // Tree mode needs special handling.
        if ($this->needTreeModeShowAll($definition, $input)) {
            return $this->callAction($environment, 'showAll');
        }

        // Check if it is a simple create-paste of a single model, if so, redirect to edit view.
        if ($this->isSimpleCreatePaste(
            $clipboard,
            $environment->getDataDefinition()->getBasicDefinition()->getDataProvider()
        )) {
            return $this->callAction($environment, 'create');
        }

        $controller    = $environment->getController();
        $source        = $this->modelIdFromParameter($input, 'source');
        $after         = $this->modelIdFromParameter($input, 'after');
        $into          = $this->modelIdFromParameter($input, 'into');
        $parentModelId = $this->modelIdFromParameter($input, 'pid');
        $items         = array();

        $controller->applyClipboardActions($source, $after, $into, $parentModelId, null, $items);

        foreach ($items as $item) {
            $clipboard->remove($item);
        }
        $clipboard->saveTo($environment);

        ViewHelpers::redirectHome($environment);
    }

    /**
     * Check permission for paste a model.
     *
     * @param ActionEvent $event The action event.
     *
     * @return bool
     */
    private function checkPermission(ActionEvent $event)
    {
        $environment     = $event->getEnvironment();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        if (true === $basicDefinition->isEditable()) {
            return true;
        }

        // TODO find a way for output the permission message.
        $event->setResponse(
            '<div style="text-align:center; font-weight:bold; padding:40px;">
                    You have no permission for paste a model.
                </div>'
        );

        return false;
    }

    /**
     * Check if paste into or after parameter is present, if not, perform showAll in tree mode.
     *
     * This is needed, as the destination is otherwise undefined.
     *
     * @param BasicDefinitionInterface $definition The current definition.
     * @param InputProviderInterface   $input      The input provider.
     *
     * @return bool
     */
    private function needTreeModeShowAll(BasicDefinitionInterface $definition, InputProviderInterface $input)
    {
        if (BasicDefinitionInterface::MODE_HIERARCHICAL !== $definition->getMode()) {
            return false;
        }

        // If destination is not known, perform showAll.
        if ($input->hasParameter('after') || $input->hasParameter('into')) {
            return false;
        }

        return true;
    }

    /**
     * Test if the current paste action is a simple paste for the passed data provider.
     *
     * @param ClipboardInterface $clipboard The clipboard instance.
     * @param string             $provider  The provider name.
     *
     * @return bool
     */
    private function isSimpleCreatePaste(ClipboardInterface $clipboard, $provider)
    {
        $filter = new Filter();
        $all    = $clipboard->fetch($filter);
        return (1 === count($all)
            && $all[0]->isCreate()
            && (null === $all[0]->getModelId())
            && $all[0]->getDataProviderName() === $provider);
    }

    /**
     * Obtain the parameter with the given name from the input provider if it exists.
     *
     * @param InputProviderInterface $input The input provider.
     * @param string                 $name  The parameter to retrieve.
     *
     * @return ModelId|null
     */
    private function modelIdFromParameter(InputProviderInterface $input, $name)
    {
        if ($input->hasParameter($name) && ($value = $input->getParameter($name))) {
            return ModelId::fromSerialized($value);
        }

        return null;
    }
}
