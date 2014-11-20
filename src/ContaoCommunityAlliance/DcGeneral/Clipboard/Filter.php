<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
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
    and item.getParentId().getDataProviderName() === variables[%1$d].getDataProviderName()
    and item.getParentId().getId() === variables[%1$d].getId()
)
EXPR;

    const PARENT_IS_NOT_EXPRESSION = <<<'EXPR'
(
    item.getParentId()
    and (
        item.getParentId().getDataProviderName() !== variables[%1$d].getDataProviderName()
        or item.getParentId().getId() !== variables[%1$d].getId()
    )
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
    public function modelIsFromProvider($modelProviderName)
    {
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
    public function modelIsNotFromProvider($modelProviderName)
    {
        $index              = count($this->variables);
        $this->expression[] = sprintf(self::MODEL_IS_NOT_FROM_PROVIDER_EXPRESSION, $index);
        $this->variables[]  = $modelProviderName;
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
    public function parentIsFromProvider($parentProviderName)
    {
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
    public function parentIsNotFromProvider($parentProviderName)
    {
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
    public function hasNoParent()
    {
        $this->expression[] = self::HAS_NO_PARENT_EXPRESSION;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And parent is.
     *
     * @param IdSerializer $parentModelId The parent id.
     *
     * @return static
     */
    public function parentIs(IdSerializer $parentModelId)
    {
        $index              = count($this->variables);
        $this->expression[] = sprintf(self::PARENT_IS_EXPRESSION, $index);
        $this->variables[]  = $parentModelId;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And parent is in.
     *
     * @param array|IdSerializer[] $parentModelIds The parent ids.
     *
     * @return static
     */
    public function parentIsIn(array $parentModelIds)
    {
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
     * @param IdSerializer $parentModelId The parent id.
     *
     * @return $this
     */
    public function parentIsNot(IdSerializer $parentModelId)
    {
        $index              = count($this->variables);
        $this->expression[] = sprintf(self::PARENT_IS_NOT_EXPRESSION, $index);
        $this->variables[]  = $parentModelId;
        $this->compiled     = null;

        return $this;
    }

    /**
     * And parent is not in.
     *
     * @param array|IdSerializer[] $parentModelIds The parent ids.
     *
     * @return static
     */
    public function parentIsNotIn(array $parentModelIds)
    {
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
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    public function accepts(ItemInterface $item)
    {
        if (null === $this->compiled) {
            $language       = new ExpressionLanguage();
            $this->compiled = $language->compile(
                $this->getExpression(),
                array('item', 'variables')
            ) . ';';
        }

        $variables = $this->variables;
        // @codingStandardsIgnoreStart
        return eval($this->compiled);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Return the expression for debugging purpose.
     *
     * @return string
     * @internal
     */
    public function getExpression()
    {
        return implode(' and ', $this->expression);
    }
}
