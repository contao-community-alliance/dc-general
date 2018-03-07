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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

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
    private $strProperty;

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
    private $arrFilterOptions;

    /**
     * Retrieve the persistent value from the input provider.
     *
     * @return null|mixed
     */
    protected function getPersistent()
    {
        $arrValue = [];
        if ($this->getSessionStorage()->has('filter')) {
            $arrValue = $this->getSessionStorage()->get('filter');
        }

        if (array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $arrValue)) {
            $arrValue = $arrValue[$this->getEnvironment()->getDataDefinition()->getName()];

            if (array_key_exists($this->getPropertyName(), $arrValue)) {
                return $arrValue[$this->getPropertyName()];
            }
        }

        return null;
    }

    /**
     * Store the persistent value in the input provider.
     *
     * @param mixed $strValue The value to store.
     *
     * @return void
     */
    protected function setPersistent($strValue)
    {
        $arrValue       = [];
        $definitionName = $this->getEnvironment()->getDataDefinition()->getName();

        if ($this->getSessionStorage()->has('filter')) {
            $arrValue = $this->getSessionStorage()->get('filter');
        }

        if (!is_array($arrValue[$definitionName])) {
            $arrValue[$this->getEnvironment()->getDataDefinition()->getName()] = [];
        }

        if ($arrValue !== null && ($strValue != 'tl_' . $this->getPropertyName())) {
            $arrValue[$definitionName][$this->getPropertyName()] = $strValue;
        } else {
            unset($arrValue[$definitionName][$this->getPropertyName()]);
        }

        $this->getSessionStorage()->set('filter', $arrValue);
    }

    /**
     * Update the local value property with data from either the session or from the input provider.
     *
     * @return void
     */
    private function updateValue()
    {
        $session = $this->getSessionStorage();
        $input   = $this->getInputProvider();
        $value   = null;

        if ($input->hasValue($this->getPropertyName()) && $this->getPanel()->getContainer()->updateValues()) {
            $value = $input->getValue($this->getPropertyName());

            $this->setPersistent($value);
        }

        if ($session->has('filter')) {
            $persistent = $this->getPersistent();
            $value      = $persistent;
        }

        if ($value !== null) {
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
        $objTempConfig = $this->getOtherConfig();
        $objTempConfig->setFields([$this->getPropertyName()]);

        $objFilterOptions = $this
            ->getEnvironment()
            ->getDataProvider()
            ->getFilterOptions($objTempConfig);

        $arrOptions = [];
        foreach ($objFilterOptions as $filterKey => $filterValue) {
            $arrOptions[(string) $filterKey] = $filterValue;
        }

        $this->arrFilterOptions = $arrOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
    {
        $this->updateValue();

        if (($objElement !== $this) && $this->getPropertyName() && (null !== $this->getValue())) {
            $arrCurrent = $objConfig->getFilter();
            if (!is_array($arrCurrent)) {
                $arrCurrent = [];
            }

            $objConfig->setFilter(
                FilterBuilder::fromArray($arrCurrent)
                    ->getFilter()
                    ->andPropertyEquals($this->getPropertyName(), $this->getValue())
                    ->getAllAsArray()
            );
        }

        // Finally load the filter options.
        if ($objElement === null) {
            $this->loadFilterOptions();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function render(ViewTemplateInterface $objTemplate)
    {
        $arrLabel = $this
            ->getEnvironment()
            ->getDataDefinition()
            ->getPropertiesDefinition()
            ->getProperty($this->getPropertyName())->getLabel();

        $arrOptions = [
            [
                'value'   => 'tl_' . $this->getPropertyName(),
                'content' => '---',
                'attributes' => ''
            ]
        ];

        $selectedValue = $this->getValue();
        foreach ($this->arrFilterOptions as $key => $value) {
            $arrOptions[] = [
                'value'      => (string) $key,
                'content'    => $value,
                'attributes' => ((string) $key === $selectedValue) ? ' selected' : ''
            ];
        }

        $objTemplate->set('label', (is_array($arrLabel) ? $arrLabel[0] : $arrLabel));
        $objTemplate->set('name', $this->getPropertyName());
        $objTemplate->set('id', $this->getPropertyName());
        $objTemplate->set('class', 'tl_select' . (($selectedValue !== null) ? ' active' : ''));
        $objTemplate->set('options', $arrOptions);
        $objTemplate->set('active', $selectedValue);

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
