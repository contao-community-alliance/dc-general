<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Default implementation of a parent child relationship.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @api
 */
class ParentChildCondition extends AbstractCondition implements ParentChildConditionInterface
{
    /**
     * The filter rules.
     *
     * @var array
     */
    protected array $filter = [];

    /**
     * The filter rules to use for an inverse filter.
     *
     * @var array
     */
    protected array $inverseFilter = [];

    /**
     * The values to use when enforcing a root condition.
     *
     * @var array
     */
    protected array $setOn = [];

    /**
     * The name of the source provider (parent).
     *
     * @var string
     */
    protected string $sourceProvider = '';

    /**
     * The name of the destination provider (child).
     *
     * @var string
     */
    protected string $destinationProvider = '';

    /**
     * Local cache property for the needed properties for filtering.
     *
     * @var list<string>|null
     */
    private ?array $neededProperties = null;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setFilterArray($value)
    {
        $this->filter = $value;
        $this->neededProperties = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getFilterArray()
    {
        return $this->filter;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setSetters($value)
    {
        $this->setOn = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getSetters()
    {
        return $this->setOn;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setInverseFilterArray($value)
    {
        $this->inverseFilter = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getInverseFilterArray()
    {
        return $this->inverseFilter;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setSourceName($value)
    {
        $this->sourceProvider = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getSourceName()
    {
        return $this->sourceProvider;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setDestinationName($value)
    {
        $this->destinationProvider = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getDestinationName()
    {
        return $this->destinationProvider;
    }

    /**
     * Apply the filter values for a given model to the given rule.
     *
     * @param array          $filter The filter rule to which the values shall get applied.
     * @param ModelInterface $model  The model to fetch the values from.
     *
     * @return array
     */
    public function parseFilter($filter, $model)
    {
        $this->guardProviderNames(null, $model);

        $applied = [
            'operation' => $filter['operation'],
        ];

        if (isset($filter['local'])) {
            /** @psalm-suppress MixedAssignment */
            $applied['property'] = $filter['local'];
        }

        if (isset($filter['remote'])) {
            /** @psalm-suppress MixedAssignment */
            $applied['value'] = $model->getProperty((string) $filter['remote']);
        }

        if (isset($filter['remote_value'])) {
            /** @psalm-suppress MixedAssignment */
            $applied['value'] = $filter['remote_value'];
        }

        if (isset($filter['value'])) {
            /** @psalm-suppress MixedAssignment */
            $applied['value'] = $filter['value'];
        }

        if (isset($filter['children'])) {
            /** @psalm-suppress MixedAssignment */
            foreach ((array) $filter['children'] as $child) {
                /** @psalm-suppress MixedArgument */
                $applied['children'][] = $this->parseFilter((array) $child, $model);
            }
        }

        return $applied;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When an empty parent model is given.
     */
    #[\Override]
    public function getFilter($parent)
    {
        $result = [];
        /** @psalm-suppress MixedAssignment */
        foreach ($this->getFilterArray() as $child) {
            /** @psalm-suppress MixedArgument */
            $result[] = $this->parseFilter((array) $child, $parent);
        }

        return $result;
    }

    /**
     * Check if the passed value is a valid setter.
     *
     * @param mixed $setter The setter.
     *
     * @return bool
     */
    private function isValidSetter($setter)
    {
        return (\is_array($setter)
            && (2 === \count($setter))
            && isset($setter['to_field'])
            && (isset($setter['from_field']) || isset($setter['value'])));
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException For invalid setters.
     */
    #[\Override]
    public function applyTo($objParent, $objChild)
    {
        $this->guardProviderNames($objChild, $objParent);

        $setters = $this->getSetters();

        if (empty($setters)) {
            throw new DcGeneralRuntimeException(
                \sprintf(
                    'No relationship setter defined from %s to %s.',
                    $this->getSourceName(),
                    $this->getDestinationName()
                )
            );
        }

        /** @psalm-suppress MixedAssignment */
        foreach ($setters as $setter) {
            if (!$this->isValidSetter($setter)) {
                throw new DcGeneralRuntimeException(
                    \sprintf(
                        'Invalid relationship setter entry, ensure it is an array containing only "to_field" and
                    one of "from_field", "value": %s',
                        \var_export($setter, true)
                    )
                );
            }

            if (isset($setter['from_field'])) {
                /** @psalm-suppress MixedArrayAccess, MixedArgument */
                $objChild->setProperty(
                    (string) $setter['to_field'],
                    $objParent->getProperty((string) $setter['from_field'])
                );

                continue;
            }

            /** @psalm-suppress MixedArrayAccess, MixedArgument */
            $objChild->setProperty((string) $setter['to_field'], $setter['value']);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException For invalid setters.
     */
    #[\Override]
    public function copyFrom($sourceModel, $destinationModel)
    {
        $this->guardProviderNames($sourceModel);
        $this->guardProviderNames($destinationModel);

        $setters = $this->getSetters();

        if (empty($setters)) {
            throw new DcGeneralRuntimeException(
                \sprintf(
                    'No relationship setter defined from %s to %s.',
                    $this->getSourceName(),
                    $this->getDestinationName()
                )
            );
        }

        /** @psalm-suppress MixedAssignment */
        foreach ($setters as $setter) {
            if (!$this->isValidSetter($setter)) {
                throw new DcGeneralRuntimeException(
                    \sprintf(
                        'Invalid relationship setter entry, ensure it is an array containing only "to_field" and
                    one of "from_field", "value": %s',
                        \var_export($setter, true)
                    )
                );
            }

            if (isset($setter['from_field'])) {
                /** @psalm-suppress MixedArrayAccess, MixedArgument */
                $destinationModel->setProperty(
                    (string) $setter['to_field'],
                    $sourceModel->getProperty((string) $setter['to_field'])
                );

                continue;
            }

            /** @psalm-suppress MixedArrayAccess, MixedArgument */
            $destinationModel->setProperty((string) $setter['to_field'], $setter['value']);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getInverseFilterFor($child)
    {
        $this->guardProviderNames($child);

        $result = [];
        /** @psalm-suppress MixedAssignment */
        /** @psalm-suppress MixedAssignment, MixedArrayAccess */
        foreach ($this->getInverseFilterArray() as $arrRule) {
            $applied = [
                'operation' => $arrRule['operation'],
            ];

            if (isset($arrRule['remote'])) {
                /** @psalm-suppress MixedAssignment, MixedArrayAccess */
                $applied['property'] = $arrRule['remote'];
            }

            if (isset($arrRule['local'])) {
                /** @psalm-suppress MixedAssignment, MixedArrayAccess, MixedArgument */
                $applied['value'] = $child->getProperty((string) $arrRule['local']);
            }

            if (isset($arrRule['value'])) {
                /** @psalm-suppress MixedAssignment, MixedArrayAccess */
                $applied['value'] = $arrRule['value'];
            }

            $result[] = $applied;
        }

        if ([] === $result) {
            return null;
        }

        return $result;
    }

    /**
     * Prepare a filter rule to be checked via checkCondition().
     *
     * @param array          $rule  The rule to prepare.
     * @param ModelInterface $child The child to be checked.
     *
     * @return array
     */
    protected function prepareRule($rule, $child)
    {
        $applied = [
            'operation' => $rule['operation'],
        ];

        if (\in_array($rule['operation'], ['AND', 'OR'])) {
            $children = [];

            /** @psalm-suppress MixedAssignment */
            foreach ($rule['children'] as $childRule) {
                /** @psalm-suppress MixedArgument */
                $children[] = $this->prepareRule((array) $childRule, $child);
            }

            $applied['children'] = $children;

            return $applied;
        }

        // Local is child property name.
        if (isset($rule['local'])) {
            /** @psalm-suppress MixedAssignment, MixedArrayAccess, MixedArgument */
            $applied['value'] = $child->getProperty((string) $rule['local']);
        } elseif (isset($rule['value'])) {
            /** @psalm-suppress MixedAssignment, MixedArrayAccess */
            $applied['value'] = $rule['value'];
        }

        // Remote is parent property name.
        if (isset($rule['remote'])) {
            /** @psalm-suppress MixedAssignment, MixedArrayAccess */
            $applied['property'] = $rule['remote'];
        } elseif (isset($rule['remote_value'])) {
            /** @psalm-suppress MixedAssignment, MixedArrayAccess */
            $applied['remote_value'] = $rule['remote_value'];
        }

        return $applied;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function matches($objParent, $objChild)
    {
        try {
            $this->guardProviderNames($objChild, $objParent);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        $filter = $this->prepareRule(
            [
                'operation' => 'AND',
                'children'  => $this->getFilterArray()
            ],
            $objChild
        );

        return static::checkCondition($objParent, $filter);
    }

    /**
     * Return the names of the needed properties for filtering.
     *
     * @param array $rule The filter rule from which the properties shall be extracted from.
     *
     * @return list<string>
     *
     * @throws \RuntimeException When an unexpected filter rule is encountered.
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    private function extractNeededProperties($rule)
    {
        if (\in_array($rule['operation'], ['AND', 'OR'])) {
            $properties = [[]];
            /** @psalm-suppress MixedAssignment */
            foreach ($rule['children'] ?? [] as $childRule) {
                /** @psalm-suppress MixedArgument */
                $properties[] = $this->extractNeededProperties((array) $childRule);
            }

            /** @psalm-suppress MixedReturnTypeCoercion */
            return \array_merge(...$properties);
        }

        // Local is child property name.
        if (isset($rule['local'])) {
            /** @var array{local: string} $rule */
            return [$rule['local']];
        }

        // Remote is parent property name.
        if (isset($rule['property'])) {
            /** @var array{property: string} $rule */
            return [$rule['property']];
        }

        throw new \RuntimeException('Unexpected filter rule ' . \var_export($rule, true));
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function neededProperties()
    {
        if (null === $this->neededProperties) {
            $this->neededProperties = $this->extractNeededProperties(
                [
                    'operation' => 'AND',
                    'children'  => $this->getFilterArray()
                ]
            );
        }

        /** @psalm-suppress MixedReturnTypeCoercion */
        return $this->neededProperties;
    }

    /**
     * Guard that the data provider names match.
     *
     * @param ModelInterface|null $child  The child model.
     * @param ModelInterface|null $parent The parent model.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When any provider name mismatches.
     */
    private function guardProviderNames($child, $parent = null)
    {
        if (null !== $child && $child->getProviderName() !== $this->destinationProvider) {
            throw new \InvalidArgumentException(
                \sprintf('provider name %s is not equal to %s', $child->getProviderName(), $this->destinationProvider)
            );
        }
        if (null !== $parent && $parent->getProviderName() !== $this->sourceProvider) {
            throw new \InvalidArgumentException(
                \sprintf('provider name %s is not equal to %s', $parent->getProviderName(), $this->sourceProvider)
            );
        }
    }
}
