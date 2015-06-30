<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeleteableException;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class ActionController handles actions on the model.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller
 */
class ActionController implements EnvironmentAwareInterface
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * Construct.
     *
     * @param EnvironmentInterface $environment The environment.
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Guard that the environment is prepared for models data definition.
     *
     * @param ModelId $modelId The model id.
     *
     * @throws DcGeneralRuntimeException If data provider name of modelId and definition does not match.
     */
    private function guardValidEnvironment(ModelId $modelId)
    {
        if ($this->environment->getDataDefinition()->getName() !== $modelId->getDataProviderName()) {
            throw new DcGeneralRuntimeException(
                sprintf(
                    'Not able to perform action. Environment is not prepared for model "%s"',
                    $modelId->getSerialized()
                )
            );
        }
    }

    /**
     * Copy a model by using a processor.
     *
     * @param ModelIdInterface  $modelId   The model id.
     * @param callable          $processor The processor.
     *
     * @return mixed
     */
    public function copy(ModelIdInterface $modelId, $processor)
    {
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider();
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        // We need to keep the original data here.
        $copyModel = $environment->getController()->createClonedModel($model);

        $preFunction = function ($environment, $model) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PreDuplicateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        $postFunction = function ($environment, $model, $originalModel) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PostDuplicateModelEvent($environment, $model, $originalModel);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };

        return call_user_func($processor, $copyModel, $model, $preFunction, $postFunction);
    }
}
