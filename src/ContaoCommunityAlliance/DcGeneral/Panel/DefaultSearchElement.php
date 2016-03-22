<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2016 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2016 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

/**
 * Default implementation of a search panel element.
 */
class DefaultSearchElement extends AbstractElement implements SearchElementInterface
{
    /**
     * The properties to be allowed to be searched on.
     *
     * @var array
     */
    protected $arrProperties;

    /**
     * The currently active property to be searched on.
     *
     * @var string
     */
    protected $strSelectedProperty;

    /**
     * The current value to be searched.
     *
     * @var mixed
     */
    protected $mixValue;

    /**
     * Retrieve the persistent value from the input provider.
     *
     * @return array
     */
    protected function getPersistent()
    {
        $arrValue = array();
        if ($this->getSessionStorage()->has('search')) {
            $arrValue = $this->getSessionStorage()->get('search');
        }

        if (array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $arrValue)) {
            return $arrValue[$this->getEnvironment()->getDataDefinition()->getName()];
        }

        return array();
    }

    /**
     * Store the persistent value in the input provider.
     *
     * @param string $strProperty The property being searched on.
     *
     * @param string $strValue    The value being searched for.
     *
     * @return void
     */
    protected function setPersistent($strProperty, $strValue)
    {
        $arrValue       = array();
        $definitionName = $this->getEnvironment()->getDataDefinition()->getName();

        if ($this->getSessionStorage()->has('search')) {
            $arrValue = $this->getSessionStorage()->get('search');
        }

        if (!empty($strValue)) {
            if (!is_array($arrValue[$definitionName])) {
                $arrValue[$definitionName] = array();
            }

            if ($strValue) {
                $arrValue[$definitionName]['field'] = $strProperty;
                $arrValue[$definitionName]['value'] = $strValue;
            } else {
                unset($arrValue[$definitionName]);
            }
        } else {
            unset($arrValue[$definitionName]);
        }

        $this->getSessionStorage()->set('search', $arrValue);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
    {
        $session = $this->getSessionStorage();
        $input   = $this->getInputProvider();
        $value   = null;
        $field   = null;

        if ($this->getPanel()->getContainer()->updateValues() && $input->hasValue('tl_field')) {
            $field = $input->getValue('tl_field');
            $value = $input->getValue('tl_value');

            $this->setPersistent($field, $value);
        } elseif ($session->has('search')) {
            $persistent = $this->getPersistent();
            if ($persistent) {
                $field = $persistent['field'];
                $value = $persistent['value'];
            }
        }

        $this->setSelectedProperty($field);
        $this->setValue($value);

        if (!($this->getSelectedProperty() && $this->getValue())) {
            return;
        }

        $arrCurrent = $objConfig->getFilter();
        if (!is_array($arrCurrent)) {
            $arrCurrent = array();
        }

        $objConfig->setFilter(
            array_merge_recursive(
                $arrCurrent,
                array(
                    array(
                        'operation' => 'AND',
                        'children'  => array(
                            array(
                                'operation' => 'LIKE',
                                'property'  => $this->getSelectedProperty(),
                                'value'     => sprintf('*%s*', $this->getValue())
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function render(ViewTemplateInterface $objTemplate)
    {
        $arrOptions = array();

        foreach ($this->getPropertyNames() as $field) {
            $arrLabel     = $this
                ->getEnvironment()
                ->getDataDefinition()
                ->getPropertiesDefinition()
                ->getProperty($field)
                ->getLabel();
            $arrOptions[] = array
            (
                'value'      => $field,
                'content'    => is_array($arrLabel) ? $arrLabel[0] : $arrLabel,
                'attributes' => ($field == $this->getSelectedProperty()) ? 'selected' : ''
            );
        }

        $objTemplate->class   = 'tl_select' . (($this->getValue() !== null) ? ' active' : '');
        $objTemplate->options = $arrOptions;
        $objTemplate->value   = $this->getValue();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addProperty($strProperty)
    {
        $this->arrProperties[] = $strProperty;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyNames()
    {
        return $this->arrProperties;
    }

    /**
     * {@inheritDoc}
     */
    public function setSelectedProperty($strProperty = '')
    {
        $this->strSelectedProperty = $strProperty;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSelectedProperty()
    {
        return $this->strSelectedProperty;
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($mixValue = null)
    {
        $this->mixValue = $mixValue;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        return $this->mixValue;
    }
}
