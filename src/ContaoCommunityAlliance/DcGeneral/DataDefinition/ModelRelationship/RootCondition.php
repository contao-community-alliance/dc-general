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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Default implementation of a root condition.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship
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
    protected $setOn;

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
    public function applyTo($objModel)
    {
        if ($this->setOn) {
            foreach ($this->setOn as $rule) {
                if (!($rule['property'] && isset($rule['value']))) {
                    throw new DcGeneralRuntimeException(
                        'Error Processing root condition, you need to specify property and value: ' . var_export(
                            $rule,
                            true
                        ),
                        1
                    );
                }

                $objModel->setProperty($rule['property'], $rule['value']);
            }
        } else {
            throw new DcGeneralRuntimeException(
                'Error Processing root condition, you need to specify root condition setters.',
                1
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function matches($objModel)
    {
        if ($this->getFilterArray()) {
            return $this->checkCondition(
                $objModel,
                array(
                    'operation' => 'AND',
                    'children'  => $this->getFilterArray()
                )
            );
        }

        return true;
    }
}
