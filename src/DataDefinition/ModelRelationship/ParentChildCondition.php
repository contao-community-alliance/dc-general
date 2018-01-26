<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Default implementation of a parent child relationship.
 */
class ParentChildCondition extends AbstractCondition implements ParentChildConditionInterface
{
    /**
     * The filter rules.
     *
     * @var array
     */
    protected $filter = array();

    /**
     * The filter rules to use for an inverse filter.
     *
     * @var array
     */
    protected $inverseFilter;

    /**
     * The values to use when enforcing a root condition.
     *
     * @var array
     */
    protected $setOn;

    /**
     * The name of the source provider (parent).
     *
     * @var string
     */
    protected $sourceProvider;

    /**
     * The name of the destination provider (child).
     *
     * @var string
     */
    protected $destinationProvider;

    /**
     * Local cache property for the needed properties for filtering.
     *
     * @var array
     */
    private $neededProperties;

    /**
     * {@inheritdoc}
     */
    public function setFilterArray($value)
    {
        $this->filter = (array) $value;
        unset($this->neededProperties);

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
        $this->setOn = (array) $value;

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
    public function setInverseFilterArray($value)
    {
        $this->inverseFilter = (array) $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInverseFilterArray()
    {
        return $this->inverseFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function setSourceName($value)
    {
        $this->sourceProvider = (string) $value;

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
     */
    public function setDestinationName($value)
    {
        $this->destinationProvider = (string) $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationName()
    {
        return $this->destinationProvider;
    }

    /**
     * Apply the filter values for a given model to the given rule.
     *
     * @param array          $filter The filter rule to which the values shall get applied.
     *
     * @param ModelInterface $model  The model to fetch the values from.
     *
     * @return array
     */
    public function parseFilter($filter, $model)
    {
        $this->guardProviderNames(null, $model);

        $arrApplied = array(
            'operation' => $filter['operation'],
        );

        if (isset($filter['local'])) {
            $arrApplied['property'] = $filter['local'];
        }

        if (isset($filter['remote'])) {
            $arrApplied['value'] = $model->getProperty($filter['remote']);
        }

        if (isset($filter['remote_value'])) {
            $arrApplied['value'] = $filter['remote_value'];
        }

        if (isset($filter['value'])) {
            $arrApplied['value'] = $filter['value'];
        }

        if (isset($filter['children'])) {
            foreach ($filter['children'] as $child) {
                $arrApplied['children'][] = $this->parseFilter($child, $model);
            }
        }

        return $arrApplied;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When an empty parent model is given.
     */
    public function getFilter($objParent)
    {
        if (!$objParent) {
            throw new DcGeneralInvalidArgumentException('No parent model passed.');
        }

        $arrResult = array();
        foreach ($this->getFilterArray() as $child) {
            $arrResult[] = $this->parseFilter($child, $objParent);
        }

        return $arrResult;
    }

    /**
     * Check if the passed value is a valid setter.
     *
     * @param array $setter The setter.
     *
     * @return bool
     */
    private function isValidSetter($setter)
    {
        return (is_array($setter)
            && (count($setter) == 2)
            && isset($setter['to_field'])
            && (isset($setter['from_field']) || isset($setter['value'])));
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException For invalid setters.
     */
    public function applyTo($objParent, $objChild)
    {
        $this->guardProviderNames($objChild, $objParent);

        $setters = $this->getSetters();

        if (empty($setters) || !is_array($setters)) {
            throw new DcGeneralRuntimeException(
                sprintf(
                    'No relationship setter defined from %s to %s.',
                    $this->getSourceName(),
                    $this->getDestinationName()
                )
            );
        }

        foreach ($setters as $setter) {
            if (!$this->isValidSetter($setter)) {
                throw new DcGeneralRuntimeException(
                    sprintf(
                        'Invalid relationship setter entry, ensure it is an array containing only "to_field" and
                    one of "from_field", "value": %s',
                        var_export($setter, true)
                    )
                );
            }

            if (isset($setter['from_field'])) {
                $objChild->setProperty($setter['to_field'], $objParent->getProperty($setter['from_field']));
            } else {
                $objChild->setProperty($setter['to_field'], $setter['value']);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException For invalid setters.
     */
    public function copyFrom($sourceModel, $destinationModel)
    {
        $this->guardProviderNames($sourceModel);
        $this->guardProviderNames($destinationModel);

        $setters = $this->getSetters();

        if (empty($setters) || !is_array($setters)) {
            throw new DcGeneralRuntimeException(
                sprintf(
                    'No relationship setter defined from %s to %s.',
                    $this->getSourceName(),
                    $this->getDestinationName()
                )
            );
        }

        foreach ($setters as $setter) {
            if (!$this->isValidSetter($setter)) {
                throw new DcGeneralRuntimeException(
                    sprintf(
                        'Invalid relationship setter entry, ensure it is an array containing only "to_field" and
                    one of "from_field", "value": %s',
                        var_export($setter, true)
                    )
                );
            }

            if (isset($setter['from_field'])) {
                $destinationModel->setProperty($setter['to_field'], $sourceModel->getProperty($setter['to_field']));
            } else {
                $destinationModel->setProperty($setter['to_field'], $setter['value']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInverseFilterFor($objChild)
    {
        $this->guardProviderNames($objChild);

        $arrResult = array();
        foreach ($this->getInverseFilterArray() as $arrRule) {
            $arrApplied = array(
                'operation' => $arrRule['operation'],
            );

            if (isset($arrRule['remote'])) {
                $arrApplied['property'] = $arrRule['remote'];
            }

            if (isset($arrRule['local'])) {
                $arrApplied['value'] = $objChild->getProperty($arrRule['local']);
            }

            if (isset($arrRule['value'])) {
                $arrApplied['value'] = $arrRule['value'];
            }

            $arrResult[] = $arrApplied;
        }

        return $arrResult;
    }

    /**
     * Prepare a filter rule to be checked via checkCondition().
     *
     * @param array          $rule  The rule to prepare.
     *
     * @param ModelInterface $child The child to be checked.
     *
     * @return array.
     */
    protected function prepareRule($rule, $child)
    {
        $applied = array(
            'operation' => $rule['operation'],
        );

        if (in_array($rule['operation'], array('AND', 'OR'))) {
            $children = array();

            foreach ($rule['children'] as $childRule) {
                $children[] = $this->prepareRule($childRule, $child);
            }

            $applied['children'] = $children;

            return $applied;
        }

        // Local is child property name.
        if (isset($rule['local'])) {
            $applied['value'] = $child->getProperty($rule['local']);
        } elseif (isset($rule['value'])) {
            $applied['value'] = $rule['value'];
        }

        // Remote is parent property name.
        if (isset($rule['remote'])) {
            $applied['property'] = $rule['remote'];
        } elseif (isset($rule['remote_value'])) {
            $applied['remote_value'] = $rule['remote_value'];
        }

        return $applied;
    }

    /**
     * {@inheritdoc}
     */
    public function matches($objParent, $objChild)
    {
        try {
            $this->guardProviderNames($objChild, $objParent);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        $filter = $this->prepareRule(
            array(
                'operation' => 'AND',
                'children'  => $this->getFilterArray()
            ),
            $objChild
        );

        return $this->checkCondition($objParent, $filter);
    }

    /**
     * Return the names of the needed properties for filtering.
     *
     * @param array $rule The filter rule from which the properties shall be extracted from.
     *
     * @return array
     *
     * @throws \RuntimeException When an unexpected filter rule is encountered.
     */
    private function extractNeededProperties($rule)
    {
        if (in_array($rule['operation'], array('AND', 'OR'))) {
            $properties = array();
            foreach ($rule['children'] as $childRule) {
                $properties[] = $this->extractNeededProperties($childRule);
            }
            return $properties;
        }

        // Local is child property name.
        if (isset($rule['local'])) {
            return $rule['local'];
        }

        // Remote is parent property name.
        if (isset($rule['property'])) {
            return $rule['property'];
        }

        throw new \RuntimeException('Unexpected filter rule ' . var_export($rule, true));
    }

    /**
     * {@inheritdoc}
     */
    public function neededProperties()
    {
        if (!isset($this->neededProperties)) {
            $this->neededProperties = $this->extractNeededProperties(
                array(
                    'operation' => 'AND',
                    'children'  => $this->getFilterArray()
                )
            );
        }

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
                sprintf('provider name %s is not equal to %s', $child->getProviderName(), $this->destinationProvider)
            );
        }
        if (null !== $parent && $parent->getProviderName() !== $this->sourceProvider) {
            throw new \InvalidArgumentException(
                sprintf('provider name %s is not equal to %s', $parent->getProviderName(), $this->sourceProvider)
            );
        }
    }
}
