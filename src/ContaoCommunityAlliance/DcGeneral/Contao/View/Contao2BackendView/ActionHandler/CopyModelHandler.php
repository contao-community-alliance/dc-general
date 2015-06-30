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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;

/**
 * Class CopyModelController handles copy action on a model.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller
 */
class CopyModelHandler implements EnvironmentAwareInterface
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
     * Create the pre function.
     *
     * @return callable
     */
    protected function createPreFunction()
    {
        $preFunction = function ($environment, $model) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PreDuplicateModelEvent($environment, $model);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };
        return $preFunction;
    }

    /**
     * Create the post function.
     *
     * @return callable
     */
    protected function createPostFunction()
    {
        $postFunction = function ($environment, $model, $originalModel) {
            /** @var EnvironmentInterface $environment */
            $copyEvent = new PostDuplicateModelEvent($environment, $model, $originalModel);
            $environment->getEventDispatcher()->dispatch(
                sprintf('%s[%s]', $copyEvent::NAME, $environment->getDataDefinition()->getName()),
                $copyEvent
            );
            $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);
        };
        return $postFunction;
    }

    /**
     * Create the default processor.
     *
     * The default process will trigger the pre function and post function and save the model into the data provider.
     *
     * @return callable
     */
    protected function createDefaultProcessor()
    {
        return function (ModelInterface $copyModel, ModelInterface $model, $preFunction, $postFunction) {
            call_user_func_array($preFunction, array($this->getEnvironment(), $copyModel, $model));

            $provider = $this->getEnvironment()->getDataProvider($copyModel->getProviderName());
            $provider->save($copyModel);

            call_user_func_array($postFunction, array($this->getEnvironment(), $copyModel, $model));
        };
    }

    /**
     * Copy a model by using a processor.
     *
     * @param ModelIdInterface  $modelId   The model id.
     * @param callable|null     $processor The processor being used to save the copy. @See createDefaultProcessor().
     *
     * @return mixed
     */
    public function handle(ModelIdInterface $modelId, $processor = null)
    {
        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider();
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        // We need to keep the original data here.
        $copyModel = $environment->getController()->createClonedModel($model);

        $processor    = $processor ?: $this->createDefaultProcessor();
        $preFunction  = $this->createPreFunction();
        $postFunction = $this->createPostFunction();

        return call_user_func($processor, $copyModel, $model, $preFunction, $postFunction);
    }
}
