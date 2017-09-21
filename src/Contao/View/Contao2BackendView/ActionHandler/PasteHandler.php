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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractHandler;

/**
 * Action handler for paste actions.
 */
class PasteHandler extends AbstractHandler
{
    /**
     * The contao framework
     *
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * PasteHandler constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Handle the action.
     *
     * @return void
     */
    public function process()
    {
        if ('BE' !== $this->framework->getMode()) {
            return;
        }

        $event = $this->getEvent();
        if ($event->getAction()->getName() !== 'paste') {
            return;
        }

        if (false === $this->checkPermission()) {
            $event->stopPropagation();

            return;
        }

        $environment = $this->getEnvironment();
        $input       = $environment->getInputProvider();
        $clipboard   = $environment->getClipboard();
        $definition  = $environment->getDataDefinition()->getBasicDefinition();

        // Tree mode needs special handling.
        if ($this->needTreeModeShowAll($definition, $input)) {
            $this->callAction('showAll');
            return;
        }

        // Check if it is a simple create-paste of a single model, if so, redirect to edit view.
        if ($this->isSimpleCreatePaste(
            $clipboard,
            $environment->getDataDefinition()->getBasicDefinition()->getDataProvider()
        )) {
            $this->callAction('create');

            return;
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
     * @return bool
     */
    private function checkPermission()
    {
        $environment     = $this->getEnvironment();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        if (true === $basicDefinition->isEditable()) {
            return true;
        }

        // TODO find a way for output the permission message.
        $this->getEvent()->setResponse(
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
