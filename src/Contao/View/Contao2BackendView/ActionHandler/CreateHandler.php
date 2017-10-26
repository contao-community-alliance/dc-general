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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EditMask;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;

/**
 * Class CreateHandler
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
 */
class CreateHandler
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * CreateHandler constructor.
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

        if ($event->getAction()->getName() !== 'create') {
            return;
        }

        $action        = $event->getAction();
        $environment   = $event->getEnvironment();
        $inputProvider = $environment->getInputProvider();

        // Only handle if we do not have a manual sorting or we know where to insert.
        // Manual sorting is handled by clipboard.
        if ($action->getName() !== 'create'
            || (ViewHelpers::getManualSortingProperty($environment)
                && !$inputProvider->hasParameter('after')
                && !$inputProvider->hasParameter('into')
            )) {
            return;
        }

        if (true !== ($response = $this->checkPermission($environment))) {
            $event->setResponse($response);
            $event->stopPropagation();

            return;
        }

        $response = $this->process($environment);
        if ($response !== false) {
            $event->setResponse($response);
        }
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
        $definition         = $environment->getDataDefinition();
        $dataProvider       = $environment->getDataProvider();
        $propertyDefinition = $definition->getPropertiesDefinition();
        $properties         = $propertyDefinition->getProperties();
        $model              = $dataProvider->getEmptyModel();
        $clone              = $dataProvider->getEmptyModel();

        // If some of the fields have a default value, set it.
        foreach ($properties as $property) {
            $propName = $property->getName();

            if ($property->getDefaultValue() !== null) {
                $model->setProperty($propName, $property->getDefaultValue());
                $clone->setProperty($propName, $property->getDefaultValue());
            }
        }

        $view = $environment->getView();
        if (!$view instanceof BaseView) {
            return false;
        }

        $editMask = new EditMask($view, $model, $clone, null, null, $view->breadcrumb());
        return $editMask->execute();
    }

    /**
     * Check permission for create a model.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string|bool
     */
    private function checkPermission(EnvironmentInterface $environment)
    {
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        if (true === $basicDefinition->isCreatable()) {
            return true;
        }

        return sprintf(
            '<div style="text-align:center; font-weight:bold; padding:40px;">
                You have no permission for create model in %s.
            </div>',
            $dataDefinition->getName()
        );
    }
}
