<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * Default implementation of a filter panel element.
 */
class DefaultFilterElement extends AbstractElement implements FilterElementInterface
{
    /**
     * Name of the property this filter reacts on.
     *
     * @var string
     */
    private $strProperty = 'string';

    /**
     * The current value of this filter.
     *
     * @var mixed
     */
    private $mixValue;

    /**
     * All valid filter options of the property.
     *
     * @var array
     */
    private $arrFilterOptions = [];

    /**
     * Retrieve the persistent value from the input provider.
     *
     * @return null|mixed
     */
    protected function getPersistent()
    {
        $values = [];
        if ($this->getSessionStorage()->has('filter')) {
            $values = $this->getSessionStorage()->get('filter');
        }

        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        if (\array_key_exists($definition->getName(), $values)) {
            $values = $values[$definition->getName()];

            if (\array_key_exists($this->getPropertyName(), $values)) {
                return $values[$this->getPropertyName()];
            }
        }

        return null;
    }

    /**
     * Store the persistent value in the input provider.
     *
     * @param mixed $value The value to store.
     *
     * @return void
     */
    protected function setPersistent($value)
    {
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $definitionName = $definition->getName();

        $values = [];

        if ($this->getSessionStorage()->has('filter')) {
            $values = $this->getSessionStorage()->get('filter');
        }

        if (isset($values[$definitionName]) && !\is_array($values[$definitionName])) {
            $values[$definition->getName()] = [];
        }

        if ((null !== $values) && ($value !== 'tl_' . $this->getPropertyName())) {
            $values[$definitionName][$this->getPropertyName()] = $value;
        } else {
            unset($values[$definitionName][$this->getPropertyName()]);
        }

        $this->getSessionStorage()->set('filter', $values);
    }

    /**
     * Update the local value property with data from either the session or from the input provider.
     *
     * @return void
     */
    private function updateValue()
    {
        $session = $this->getSessionStorage();
        assert($session instanceof SessionStorageInterface);

        $input = $this->getInputProvider();
        assert($input instanceof InputProviderInterface);

        $value   = null;

        if ('1' !== $input->getValue('filter_reset')) {
            if ($input->hasValue($this->getPropertyName()) && $this->getPanel()->getContainer()->updateValues()) {
                $value = $input->getValue($this->getPropertyName());

                $this->setPersistent($value);
            }

            if ($session->has('filter')) {
                $persistent = $this->getPersistent();
                $value      = $persistent;
            }
        } else {
            $this->setPersistent(null);
        }

        if (null !== $value) {
            $this->setValue($value);
        }
    }

    /**
     * Load the filter options from the configured property.
     *
     * @return void
     */
    private function loadFilterOptions()
    {
        $otherConfig = $this->getOtherConfig();
        $otherConfig->setFields([$this->getPropertyName()]);

        $dataProvider = $this->getEnvironment()->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        $filterOptions = $dataProvider->getFilterOptions($otherConfig);

        $options = [];
        foreach ($filterOptions as $filterKey => $filterValue) {
            $options[$filterKey] = $filterValue;
        }

        $this->arrFilterOptions = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(ConfigInterface $config, PanelElementInterface $element = null)
    {
        $this->updateValue();

        if (($element !== $this) && $this->getPropertyName() && (null !== $this->getValue())) {
            $current = $config->getFilter();
            if (!\is_array($current)) {
                $current = [];
            }

            $config->setFilter(
                FilterBuilder::fromArray($current)
                    ->getFilter()
                    ->andPropertyEquals($this->getPropertyName(), $this->getValue())
                    ->getAllAsArray()
            );
        }

        // Finally load the filter options.
        if (null === $element) {
            $this->loadFilterOptions();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function render(ViewTemplateInterface $viewTemplate)
    {
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);
        $translator = $this->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);
        $properties = $definition->getPropertiesDefinition();
        $field      = $this->getPropertyName();
        $label      = $translator->translate($properties->getProperty($field)->getLabel(), $definition->getName());

        $options = [
            [
                'value'   => 'tl_' . $field,
                'content' => '---',
                'attributes' => ''
            ]
        ];

        $selectedValue = $this->getValue();
        foreach ($this->arrFilterOptions as $key => $value) {
            $options[] = [
                'value'      => (string) $key,
                'content'    => $value,
                'attributes' => ((string) $key === $selectedValue) ? ' selected' : ''
            ];
        }

        $viewTemplate->set('label', $label);
        $viewTemplate->set('name', $this->getPropertyName());
        $viewTemplate->set('id', $this->getPropertyName());
        $viewTemplate->set('class', 'tl_select' . ((null !== $selectedValue) ? ' active' : ''));
        $viewTemplate->set('options', $options);
        $viewTemplate->set('active', $selectedValue);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPropertyName($strProperty)
    {
        $this->strProperty = $strProperty;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyName()
    {
        return $this->strProperty;
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($mixValue)
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
