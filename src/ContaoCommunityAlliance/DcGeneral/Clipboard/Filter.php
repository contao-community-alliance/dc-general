<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class Filter.
 */
class Filter implements FilterInterface
{
    /**
     * The expression parts.
     *
     * @var array
     */
    private $expression = array();

    /**
     * The expression variables.
     *
     * @var array
     */
    private $variables = array();

    /**
     * Pre-compiled expression.
     *
     * @var string
     */
    private $compiled = null;

    const MODEL_IS_FROM_PROVIDER_EXPRESSION = <<<'EXPR'
(
    item.getModelId()
    and item.getModelId().getDataProviderName() === variables[%d]
)
EXPR;

    const MODEL_IS_NOT_FROM_PROVIDER_EXPRESSION = <<<'EXPR'
(
    item.getModelId()
    and item.getModelId().getDataProviderName() !== variables[%d]
)
EXPR;

    const MODEL_IS_EXPRESSION = <<<'EXPR'
(
    item.getModelId().equals(variables[%d])
)
EXPR;

    const MODEL_IS_NOT_EXPRESSION = <<<'EXPR'
(
    !item.getModelId().equals(variables[%d])
)
EXPR;

    const PARENT_IS_FROM_PROVIDER_EXPRESSION = <<<'EXPR'
(
    item.getParentId()
    and item.getParentId().getDataProviderName() === variables[%d]
)
EXPR;

    const PARENT_IS_NOT_FROM_PROVIDER_EXPRESSION = <<<'EXPR'
(
    item.getParentId()
    and item.getParentId().getDataProviderName() !== variables[%d]
)
EXPR;

    const HAS_NO_PARENT_EXPRESSION = <<<'EXPR'
(
    !item.getParentId()
)
EXPR;

    const PARENT_IS_EXPRESSION = <<<'EXPR'
(
    item.getParentId()
    and item.getParentId().equals(variables[%d])
)
EXPR;

    const PARENT_IS_NOT_EXPRESSION = <<<'EXPR'
(
    item.getParentId()
    and !item.getParentId().equals(variables[%d])
)
EXPR;

    const ACTION_IS_EXPRESSION = <<<'EXPR'
(
    item.getAction() === variables[%d]
)
EXPR;

    const ACTION_IS_NOT_EXPRESSION = <<<'EXPR'
(
    item.getAction() !== variables[%d]
)
EXPR;

    /**
     * Factory method.
     *
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * And model is from provider.
     *
     * @param string $modelProviderName The provider name.
     *
     * @return static
     */
    public function andModelIsFromProvider($modelProviderName)
    {
        $this->modelIsFromProvider('and', $modelProviderName);

        return $this;
    }

    /**
     * Or model is from provider.
     *
     * @param string $modelProviderName The provider name.
     *
     * @return static
     */
    public function orModelIsFromProvider($modelProviderName)
    {
        $this->modelIsFromProvider('or', $modelProviderName);

        return $this;
    }

    /**
     * Add model is from provider fragment.
     *
     * @param string $conjunction       AND or OR.
     * @param string $modelProviderName The provider name.
     *
     * @return static
     */
    private function modelIsFromProvider($conjunction, $modelProviderName)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::MODEL_IS_FROM_PROVIDER_EXPRESSION, $index);
        $this->variables[]  = $modelProviderName;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And model is not from provider.
     *
     * @param string $modelProviderName The provider name.
     *
     * @return static
     */
    public function andModelIsNotFromProvider($modelProviderName)
    {
        $this->modelIsNotFromProvider('and', $modelProviderName);

        return $this;
    }

    /**
     * Or model is not from provider.
     *
     * @param string $modelProviderName The provider name.
     *
     * @return static
     */
    public function orModelIsNotFromProvider($modelProviderName)
    {
        $this->modelIsNotFromProvider('or', $modelProviderName);

        return $this;
    }

    /**
     * Add model is not from provider.
     *
     * @param string $conjunction       AND or OR.
     * @param string $modelProviderName The provider name.
     *
     * @return static
     */
    private function modelIsNotFromProvider($conjunction, $modelProviderName)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::MODEL_IS_NOT_FROM_PROVIDER_EXPRESSION, $index);
        $this->variables[]  = $modelProviderName;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And model is.
     *
     * @param ModelId $modelId The model id.
     *
     * @return static
     */
    public function andModelIs(ModelId $modelId)
    {
        $this->modelIs('and', $modelId);

        return $this;
    }

    /**
     * Or model is.
     *
     * @param ModelId $modelId The model id.
     *
     * @return static
     */
    public function orModelIs(ModelId $modelId)
    {
        $this->modelIs('or', $modelId);

        return $this;
    }

    /**
     * Add model is.
     *
     * @param string  $conjunction AND or OR.
     * @param ModelId $modelId     The model id.
     *
     * @return static
     */
    private function modelIs($conjunction, ModelId $modelId)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::MODEL_IS_EXPRESSION, $index);
        $this->variables[]  = $modelId;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And model is not.
     *
     * @param ModelId $modelId The model id.
     *
     * @return static
     */
    public function andModelIsNot(ModelId $modelId)
    {
        $this->modelIsNot('and', $modelId);

        return $this;
    }

    /**
     * Or model is not.
     *
     * @param ModelId $modelId The model id.
     *
     * @return static
     */
    public function orModelIsNot(ModelId $modelId)
    {
        $this->modelIsNot('or', $modelId);

        return $this;
    }

    /**
     * Add model is not.
     *
     * @param string  $conjunction AND or OR.
     * @param ModelId $modelId     The model id.
     *
     * @return static
     */
    private function modelIsNot($conjunction, ModelId $modelId)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::MODEL_IS_NOT_EXPRESSION, $index);
        $this->variables[]  = $modelId;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And parent is from provider.
     *
     * @param string $parentProviderName The parent provider name.
     *
     * @return static
     */
    public function andParentIsFromProvider($parentProviderName)
    {
        $this->parentIsFromProvider('and', $parentProviderName);

        return $this;
    }

    /**
     * Or parent is from provider.
     *
     * @param string $parentProviderName The parent provider name.
     *
     * @return static
     */
    public function orParentIsFromProvider($parentProviderName)
    {
        $this->parentIsFromProvider('or', $parentProviderName);

        return $this;
    }

    /**
     * Add parent is from provider.
     *
     * @param string $conjunction        AND or OR.
     * @param string $parentProviderName The parent provider name.
     *
     * @return static
     */
    private function parentIsFromProvider($conjunction, $parentProviderName)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::PARENT_IS_FROM_PROVIDER_EXPRESSION, $index);
        $this->variables[]  = $parentProviderName;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And parent is not from provider.
     *
     * @param string $parentProviderName The parent provider name.
     *
     * @return static
     */
    public function andParentIsNotFromProvider($parentProviderName)
    {
        $this->parentIsNotFromProvider('and', $parentProviderName);

        return $this;
    }

    /**
     * Or parent is not from provider.
     *
     * @param string $parentProviderName The parent provider name.
     *
     * @return static
     */
    public function orParentIsNotFromProvider($parentProviderName)
    {
        $this->parentIsNotFromProvider('or', $parentProviderName);

        return $this;
    }

    /**
     * And parent is not from provider.
     *
     * @param string $conjunction        AND or OR.
     * @param string $parentProviderName The parent provider name.
     *
     * @return static
     */
    private function parentIsNotFromProvider($conjunction, $parentProviderName)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::PARENT_IS_NOT_FROM_PROVIDER_EXPRESSION, $index);
        $this->variables[]  = $parentProviderName;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And has no parent.
     *
     * @return static
     */
    public function andHasNoParent()
    {
        $this->hasNoParent('and');

        return $this;
    }

    /**
     * Or has no parent.
     *
     * @return static
     */
    public function orHasNoParent()
    {
        $this->hasNoParent('or');

        return $this;
    }

    /**
     * Add has no parent.
     *
     * @param string $conjunction AND or OR.
     *
     * @return static
     */
    private function hasNoParent($conjunction)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $this->expression[] = self::HAS_NO_PARENT_EXPRESSION;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And parent is.
     *
     * @param ModelId $parentModelId The parent id.
     *
     * @return static
     */
    public function andParentIs(ModelId $parentModelId)
    {
        $this->parentIs('and', $parentModelId);

        return $this;
    }

    /**
     * Or parent is.
     *
     * @param ModelId $parentModelId The parent id.
     *
     * @return static
     */
    public function orParentIs(ModelId $parentModelId)
    {
        $this->parentIs('or', $parentModelId);

        return $this;
    }

    /**
     * Add parent is.
     *
     * @param string  $conjunction   AND or OR.
     * @param ModelId $parentModelId The parent id.
     *
     * @return static
     */
    private function parentIs($conjunction, ModelId $parentModelId)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::PARENT_IS_EXPRESSION, $index);
        $this->variables[]  = $parentModelId;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And parent is in.
     *
     * @param array|ModelId[] $parentModelIds The parent ids.
     *
     * @return static
     */
    public function andParentIsIn(array $parentModelIds)
    {
        $this->parentIsIn('and', $parentModelIds);

        return $this;
    }

    /**
     * Or parent is in.
     *
     * @param array|ModelId[] $parentModelIds The parent ids.
     *
     * @return static
     */
    public function orParentIsIn(array $parentModelIds)
    {
        $this->parentIsIn('or', $parentModelIds);

        return $this;
    }

    /**
     * Add parent is in.
     *
     * @param string          $conjunction    AND or OR.
     * @param array|ModelId[] $parentModelIds The parent ids.
     *
     * @return static
     */
    private function parentIsIn($conjunction, array $parentModelIds)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $expression = array();
        foreach ($parentModelIds as $parentModelId) {
            $index             = count($this->variables);
            $expression[]      = sprintf(self::PARENT_IS_EXPRESSION, $index);
            $this->variables[] = $parentModelId;
        }
        $this->expression[] = '(' . implode(' or ', $expression) . ')';
        $this->compiled     = null;

        return $this;
    }

    /**
     * And parent is not.
     *
     * @param ModelId $parentModelId The parent id.
     *
     * @return static
     */
    public function andParentIsNot(ModelId $parentModelId)
    {
        $this->parentIsNot('and', $parentModelId);

        return $this;
    }

    /**
     * Or parent is not.
     *
     * @param ModelId $parentModelId The parent id.
     *
     * @return static
     */
    public function orParentIsNot(ModelId $parentModelId)
    {
        $this->parentIsNot('and', $parentModelId);

        return $this;
    }

    /**
     * Add parent is not.
     *
     * @param string  $conjunction   AND or OR.
     * @param ModelId $parentModelId The parent id.
     *
     * @return static
     */
    private function parentIsNot($conjunction, ModelId $parentModelId)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::PARENT_IS_NOT_EXPRESSION, $index);
        $this->variables[]  = $parentModelId;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And parent is not in.
     *
     * @param array|ModelId[] $parentModelIds The parent ids.
     *
     * @return static
     */
    public function andParentIsNotIn(array $parentModelIds)
    {
        $this->parentIsNotIn('and', $parentModelIds);

        return $this;
    }

    /**
     * Or parent is not in.
     *
     * @param array|ModelId[] $parentModelIds The parent ids.
     *
     * @return static
     */
    public function orParentIsNotIn(array $parentModelIds)
    {
        $this->parentIsNotIn('or', $parentModelIds);

        return $this;
    }

    /**
     * Add parent is not in.
     *
     * @param string          $conjunction    AND or OR.
     * @param array|ModelId[] $parentModelIds The parent ids.
     *
     * @return static
     */
    private function parentIsNotIn($conjunction, array $parentModelIds)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $expression = array();
        foreach ($parentModelIds as $parentModelId) {
            $index             = count($this->variables);
            $expression[]      = sprintf(self::PARENT_IS_NOT_EXPRESSION, $index);
            $this->variables[] = $parentModelId;
        }
        $this->expression[] = '(' . implode(' or ', $expression) . ')';
        $this->compiled     = null;

        return $this;
    }

    /**
     * And action is.
     *
     * @param string $action The action name.
     *
     * @return static
     */
    public function andActionIs($action)
    {
        $this->actionIs('and', $action);

        return $this;
    }

    /**
     * Or action is.
     *
     * @param string $action The action name.
     *
     * @return static
     */
    public function orActionIs($action)
    {
        $this->actionIs('or', $action);

        return $this;
    }

    /**
     * Add action is.
     *
     * @param string $conjunction AND or OR.
     * @param string $action      The action name.
     *
     * @return static
     */
    private function actionIs($conjunction, $action)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::ACTION_IS_EXPRESSION, $index);
        $this->variables[]  = $action;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And action is in.
     *
     * @param array|string[] $actions The action names.
     *
     * @return static
     */
    public function andActionIsIn(array $actions)
    {
        $this->actionIsIn('and', $actions);

        return $this;
    }

    /**
     * Or action is in.
     *
     * @param array|string[] $actions The action names.
     *
     * @return static
     */
    public function orActionIsIn(array $actions)
    {
        $this->actionIsIn('or', $actions);

        return $this;
    }

    /**
     * Add action is in.
     *
     * @param string         $conjunction AND or OR.
     * @param array|string[] $actions     The action names.
     *
     * @return static
     */
    private function actionIsIn($conjunction, array $actions)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $expression = array();
        foreach ($actions as $action) {
            $index             = count($this->variables);
            $expression[]      = sprintf(self::ACTION_IS_EXPRESSION, $index);
            $this->variables[] = $action;
        }
        $this->expression[] = '(' . implode(' or ', $expression) . ')';
        $this->compiled     = null;

        return $this;
    }

    /**
     * And action is.
     *
     * @param string $action The action name.
     *
     * @return static
     */
    public function andActionIsNot($action)
    {
        $this->actionIsNot('and', $action);

        return $this;
    }

    /**
     * Or action is.
     *
     * @param string $action The action name.
     *
     * @return static
     */
    public function orActionIsNot($action)
    {
        $this->actionIsNot('or', $action);

        return $this;
    }

    /**
     * Add action is.
     *
     * @param string $conjunction AND or OR.
     * @param string $action      The action name.
     *
     * @return static
     */
    private function actionIsNot($conjunction, $action)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $index              = count($this->variables);
        $this->expression[] = sprintf(self::ACTION_IS_NOT_EXPRESSION, $index);
        $this->variables[]  = $action;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And action is not in.
     *
     * @param array|string[] $actions The action names.
     *
     * @return static
     */
    public function andActionIsNotIn(array $actions)
    {
        $this->actionIsNotIn('and', $actions);

        return $this;
    }

    /**
     * Or action is not in.
     *
     * @param array|string[] $actions The action names.
     *
     * @return static
     */
    public function orActionIsNotIn(array $actions)
    {
        $this->actionIsNotIn('or', $actions);

        return $this;
    }

    /**
     * Add action is not in.
     *
     * @param string         $conjunction AND or OR.
     * @param array|string[] $actions     The action names.
     *
     * @return static
     */
    private function actionIsNotIn($conjunction, array $actions)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $expression = array();
        foreach ($actions as $action) {
            $index             = count($this->variables);
            $expression[]      = sprintf(self::ACTION_IS_NOT_EXPRESSION, $index);
            $this->variables[] = $action;
        }
        $this->expression[] = '(' . implode(' or ', $expression) . ')';
        $this->compiled     = null;

        return $this;
    }

    /**
     * And sub filter.
     *
     * @param Filter $filter The sub filter.
     *
     * @return static
     */
    public function andSub(Filter $filter)
    {
        $this->sub('and', $filter);

        return $this;
    }

    /**
     * Or sub filter.
     *
     * @param Filter $filter The sub filter.
     *
     * @return static
     */
    public function orSub(Filter $filter)
    {
        $this->sub('or', $filter);

        return $this;
    }

    /**
     * Add sub filter.
     *
     * @param string $conjunction AND or OR.
     * @param Filter $filter      The sub filter.
     *
     * @return static
     */
    private function sub($conjunction, Filter $filter)
    {
        if (!empty($this->expression)) {
            $this->expression[] = $conjunction;
        }

        $expression = $filter->getExpression();
        $variables  = $filter->getVariables();

        $index      = count($this->variables);
        $expression = str_replace('variables', 'variables[' . $index . ']', $expression);

        $this->expression[] = '(' . $expression . ')';
        $this->variables[]  = $variables;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    public function accepts(ItemInterface $item)
    {
        if (null === $this->compiled) {
            $language       = new ExpressionLanguage();
            $expression     = $this->getExpression();
            $this->compiled = sprintf(
                'return %s;',
                $language->compile(
                    $expression,
                    array('item', 'variables')
                )
            );
        }

        $variables = $this->variables;
        // @codingStandardsIgnoreStart
        return eval($this->compiled);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Return the expression.
     *
     * @return string
     * @internal
     */
    public function getExpression()
    {
        return $this->expression ? implode(' ', $this->expression) : 'true';
    }

    /**
     * Return the variables.
     *
     * @return array
     * @internal
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
