<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Default implementation of a root condition.
 */
class RootCondition extends AbstractCondition implements RootConditionInterface
{
    /**
     * The filter rules to use.
     *
     * @var array
     */
    protected $filter;

    /**
     * The setter information to use when a model shall get marked as root item.
     *
     * @var array
     */
    protected $setOn = [];

    /**
     * The name of the table this condition is being applied to.
     *
     * @var string
     */
    protected $sourceProvider;

    /**
     * {@inheritdoc}
     */
    public function setFilterArray($value)
    {
        $this->filter = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterArray()
    {
        return $this->filter;
    }

    /**
     * {@inheritdoc}
     */
    public function setSetters($value)
    {
        $this->setOn = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSetters()
    {
        return $this->setOn;
    }

    /**
     * {@inheritdoc}
     */
    public function setSourceName($value)
    {
        $this->sourceProvider = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceName()
    {
        return $this->sourceProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When an incomplete rule is encountered in the setters.
     */
    public function applyTo($model)
    {
        $this->guardProviderName($model);

        if ($this->setOn) {
            foreach ($this->setOn as $rule) {
                if (!($rule['property'] && isset($rule['value']))) {
                    throw new DcGeneralRuntimeException(
                        'Error Processing root condition, you need to specify property and value: ' . \var_export(
                            $rule,
                            true
                        ),
                        1
                    );
                }

                $model->setProperty($rule['property'], $rule['value']);
            }

            return $this;
        }

        throw new DcGeneralRuntimeException(
            'Error Processing root condition, you need to specify root condition setters.',
            1
        );
    }

    /**
     * {@inheritdoc}
     */
    public function matches($model)
    {
        try {
            $this->guardProviderName($model);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        if ($this->getFilterArray()) {
            return static::checkCondition(
                $model,
                [
                    'operation' => 'AND',
                    'children'  => $this->getFilterArray()
                ]
            );
        }

        return true;
    }

    /**
     * Guard that the data provider name matches.
     *
     * @param ModelInterface $model The model.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When any provider name mismatches.
     */
    private function guardProviderName($model)
    {
        if ($model->getProviderName() !== $this->sourceProvider) {
            throw new \InvalidArgumentException(
                \sprintf('provider name %s is not equal to %s', $model->getProviderName(), $this->getSourceName())
            );
        }
    }
}
