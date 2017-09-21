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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * An abstract condition chain.
 */
abstract class AbstractConditionChain implements ConditionChainInterface
{
    /**
     * The list of conditions.
     *
     * @var ConditionInterface[]
     */
    protected $conditions = array();

    /**
     * The conjunction mode.
     *
     * @var string
     */
    protected $conjunction = self::AND_CONJUNCTION;

    /**
     * Create a new condition chain.
     *
     * @param array  $conditions  The conditions to initialize the chain with (optional).
     *
     * @param string $conjunction The conjunction this chain contains (defaults to AND).
     */
    public function __construct(array $conditions = array(), $conjunction = self::AND_CONJUNCTION)
    {
        $this->addConditions($conditions);
        $this->setConjunction($conjunction);
    }

    /**
     * {@inheritdoc}
     */
    public function clearConditions()
    {
        $this->conditions = array();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConditions(array $conditions)
    {
        $this->clearConditions();
        $this->addConditions($conditions);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addConditions(array $conditions)
    {
        foreach ($conditions as $condition) {
            $this->addCondition($condition);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCondition(ConditionInterface $condition)
    {
        $hash = spl_object_hash($condition);

        $this->conditions[$hash] = $condition;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCondition(ConditionInterface $condition)
    {
        $hash = spl_object_hash($condition);
        unset($this->conditions[$hash]);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        return array_values($this->conditions);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When the conjunction is neither AND nor OR.
     */
    public function setConjunction($conjunction)
    {
        if ($conjunction != static::AND_CONJUNCTION && $conjunction != static::OR_CONJUNCTION) {
            throw new DcGeneralInvalidArgumentException(
                'Conjunction must be ConditionChainInterface::AND_CONJUNCTION ' .
                'or ConditionChainInterface::OR_CONJUNCTION'
            );
        }

        $this->conjunction = (string) $conjunction;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConjunction()
    {
        return $this->conjunction;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $conditions = array();
        foreach ($this->conditions as $condition) {
            $bobaFett = clone $condition;

            $conditions[spl_object_hash($bobaFett)] = $bobaFett;
        }
        $this->conditions = $conditions;
    }
}
