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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * Default implementation of a search panel element.
 *
 * @api
 */
class DefaultSearchElement extends AbstractElement implements SearchElementInterface
{
    /**
     * The properties to be allowed to be searched on.
     *
     * @var array
     */
    private array $arrProperties = [];

    /**
     * The currently active property to be searched on.
     *
     * @var string
     */
    private string $strSelectedProperty = '';

    /**
     * The current value to be searched.
     *
     * @var mixed
     */
    private mixed $mixValue = null;

    /**
     * Retrieve the persistent value from the input provider.
     *
     * @return array
     */
    protected function getPersistent()
    {
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $values = [];
        if ($this->getSessionStorage()->has('search')) {
            /** @psalm-suppress MixedAssignment */
            $values = $this->getSessionStorage()->get('search');
        }

        /** @psalm-suppress MixedArgument */
        if (\array_key_exists($definition->getName(), $values)) {
            /** @psalm-suppress MixedArrayAccess, MixedReturnStatement */
            return $values[$definition->getName()];
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
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $definitionName = $definition->getName();

        $values = [];
        if ($this->getSessionStorage()->has('search')) {
            /** @psalm-suppress MixedAssignment */
            $values = $this->getSessionStorage()->get('search');
        }

        if (!empty($searchValue)) {
            if (isset($values[$definitionName]) && !\is_array($values[$definitionName])) {
                /** @psalm-suppress MixedArrayAssignment */
                $values[$definitionName] = [];
            }

            /** @psalm-suppress MixedArrayAccess, MixedArrayAssignment */
            $values[$definitionName]['field'] = $propertyName;
            /** @psalm-suppress MixedArrayAccess, MixedArrayAssignment */
            $values[$definitionName]['value'] = $searchValue;
        } else {
            /** @psalm-suppress MixedArrayAccess */
            unset($values[$definitionName]);
        }

        $this->getSessionStorage()->set('search', $values);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function initialize(ConfigInterface $config, ?PanelElementInterface $element = null)
    {
        $session = $this->getSessionStorage();
        $input   = $this->getInputProvider();
        assert($input instanceof InputProviderInterface);

        $value = '';
        $field = '';

        if ('1' !== $input->getValue('filter_reset')) {
            if ($input->hasValue('tl_field') && $this->getPanel()->getContainer()->updateValues()) {
                /** @psalm-suppress MixedAssignment */
                $field = $input->getValue('tl_field');
                /** @psalm-suppress MixedAssignment */
                $value = $input->getValue('tl_value');

                /** @psalm-suppress MixedArgument, MixedArgument */
                $this->setPersistent($field, $value);
            } elseif ($session->has('search')) {
                $persistent = $this->getPersistent();
                if ($persistent) {
                    /** @psalm-suppress MixedAssignment */
                    $field = $persistent['field'];
                    /** @psalm-suppress MixedAssignment */
                    $value = $persistent['value'];
                }
            }

            /** @psalm-suppress MixedArgument */
            $this->setSelectedProperty($field);
            /** @psalm-suppress MixedArgument */
            $this->setValue($value);
        } else {
            $this->setPersistent('', '');
        }

        if (!($this->getSelectedProperty() && $this->getValue())) {
            return;
        }

        $currents = $config->getFilter();
        if (!\is_array($currents)) {
            $currents = [];
        }

        $config->setFilter(
            \array_merge_recursive(
                $currents,
                [
                    [
                        'operation' => 'LIKE',
                        'property'  => $this->getSelectedProperty(),
                        'value'     => \sprintf('*%s*', $this->getValue())
                    ]
                ]
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function render(ViewTemplateInterface $viewTemplate)
    {
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $options = [];
        $translator = $this->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);
        $properties = $definition->getPropertiesDefinition();
        foreach ($this->getPropertyNames() as $field) {
            $label   = $translator->translate($properties->getProperty($field)->getLabel(), $definition->getName());
            $options[] = [
                'value'      => $field,
                'content'    => $label,
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
    #[\Override]
    public function addProperty($strProperty)
    {
        $this->arrProperties[] = $strProperty;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    #[\Override]
    public function getPropertyNames(): array
    {
        return $this->arrProperties;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setSelectedProperty($strProperty = '')
    {
        $this->strSelectedProperty = $strProperty;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getSelectedProperty()
    {
        return $this->strSelectedProperty;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setValue($mixValue = null)
    {
        $this->mixValue = $mixValue;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getValue()
    {
        /** @psalm-suppress MixedReturnStatement */
        return $this->mixValue;
    }
}
