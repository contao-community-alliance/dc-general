<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
    private $arrProperties;

    /**
     * The currently active property to be searched on.
     *
     * @var string
     */
    private $strSelectedProperty;

    /**
     * The current value to be searched.
     *
     * @var mixed
     */
    private $mixValue;

    /**
     * Retrieve the persistent value from the input provider.
     *
     * @return array
     */
    protected function getPersistent()
    {
        $values = [];
        if ($this->getSessionStorage()->has('search')) {
            $values = $this->getSessionStorage()->get('search');
        }

        if (\array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $values)) {
            return $values[$this->getEnvironment()->getDataDefinition()->getName()];
        }

        return [];
    }

    /**
     * Store the persistent value in the input provider.
     *
     * @param string $propertyName The property being searched on.
     * @param string $searchValue  The value being searched for.
     *
     * @return void
     */
    protected function setPersistent($propertyName, $searchValue)
    {
        $values         = [];
        $definitionName = $this->getEnvironment()->getDataDefinition()->getName();

        if ($this->getSessionStorage()->has('search')) {
            $values = $this->getSessionStorage()->get('search');
        }

        if (!empty($searchValue)) {
            if (!\is_array($values[$definitionName])) {
                $values[$definitionName] = [];
            }

            if ($searchValue) {
                $values[$definitionName]['field'] = $propertyName;
                $values[$definitionName]['value'] = $searchValue;
            } else {
                unset($values[$definitionName]);
            }
        } else {
            unset($values[$definitionName]);
        }

        $this->getSessionStorage()->set('search', $values);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ConfigInterface $filterConfig, PanelElementInterface $objElement = null)
    {
        $session = $this->getSessionStorage();
        $input   = $this->getInputProvider();
        $value   = null;
        $field   = null;
        
        if ('1' !== $this->getEnvironment()->getInputProvider()->getValue('filter_reset')) {

            if ($input->hasValue('tl_field') && $this->getPanel()->getContainer()->updateValues()) {
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

        } else {
          $this->setPersistent(null, null);
        }

        if (!($this->getSelectedProperty() && $this->getValue())) {
            return;
        }

        $currents = $filterConfig->getFilter();
        if (!\is_array($currents)) {
            $currents = [];
        }

        $filterConfig->setFilter(
            \array_merge_recursive(
                $currents,
                [
                    [
                        'operation' => 'AND',
                        'children'  => [
                            [
                                'operation' => 'LIKE',
                                'property'  => $this->getSelectedProperty(),
                                'value'     => \sprintf('*%s*', $this->getValue())
                            ]
                        ]
                    ]
                ]
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function render(ViewTemplateInterface $viewTemplate)
    {
        $options = [];

        foreach ($this->getPropertyNames() as $field) {
            $lLabels   = $this
                ->getEnvironment()
                ->getDataDefinition()
                ->getPropertiesDefinition()
                ->getProperty($field)
                ->getLabel();
            $options[] = [
                    'value'      => $field,
                    'content'    => \is_array($lLabels) ? $lLabels[0] : $lLabels,
                    'attributes' => ($field === $this->getSelectedProperty()) ? ' selected' : ''
                ];
        }

        $viewTemplate->set('class', 'tl_select' . (!empty($this->getValue()) ? ' active' : ''));
        $viewTemplate->set('options', $options);
        $viewTemplate->set('value', $this->getValue());

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addProperty($propertyName)
    {
        $this->arrProperties[] = $propertyName;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyNames(): array
    {
        return $this->arrProperties;
    }

    /**
     * {@inheritDoc}
     */
    public function setSelectedProperty($propertyName = '')
    {
        $this->strSelectedProperty = $propertyName;

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
