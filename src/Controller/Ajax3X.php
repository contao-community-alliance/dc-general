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
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\Dbafs;
use Contao\FileSelector;
use Contao\PageSelector;
use Contao\StringUtil;
use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

use function array_merge;
use function implode;
use function is_string;
use function preg_replace;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function urldecode;

/**
 * Class GeneralAjax - General purpose Ajax handler for "executePostActions" in Contao 3.X as we can not use the default
 * Contao handling.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 */
class Ajax3X extends Ajax
{
    /**
     * Get the widget instance.
     *
     * @param string      $fieldName     The property name.
     * @param string|null $serializedId  The serialized id of the model.
     * @param string      $propertyValue The property value.
     *
     * @return Widget|null
     */
    protected function getWidget($fieldName, $serializedId, $propertyValue)
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $property       = $definition->getPropertiesDefinition()->getProperty($fieldName);
        $propertyValues = new PropertyValueBag();

        if (null !== $serializedId) {
            $model = $this->getModelFromSerializedId($serializedId);
        } else {
            $dataProvider = $environment->getDataProvider();
            assert($dataProvider instanceof DataProviderInterface);

            $model = $dataProvider->getEmptyModel();
        }

        $widgetManager = new ContaoWidgetManager($environment, $model);

        // Process input and update changed properties.
        $treeType      = substr($property->getWidgetType(), 0, 4);
        $propertyValue = $this->getTreeValue($treeType, $propertyValue);
        if (('file' === $treeType) || ('page' === $treeType)) {
            $extra = $property->getExtra();
            if (!isset($extra['multiple'])) {
                $propertyValue = $propertyValue[0] ?? '';
            } else {
                $propertyValue = implode(',', $propertyValue);
            }
        }

        $propertyValues->setPropertyValue($fieldName, $propertyValue);
        $widgetManager->processInput($propertyValues);
        $model->setProperty($fieldName, $propertyValues->getPropertyValue($fieldName));

        return $widgetManager->getWidget($fieldName);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ResponseException Throws a response exception.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    #[\Override]
    protected function loadPagetree()
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        $session = $environment->getSessionStorage();
        assert($session instanceof SessionStorageInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        /** @psalm-suppress MixedAssignment */
        $field  = $input->getValue('field');
        $name   = (string) $input->getValue('name');
        $level  = (int) $input->getValue('level');
        $rootId = (string) $input->getValue('id');

        $ajaxId   = (string) preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $rootId);
        $ajaxKey  = str_replace('_' . $ajaxId, '', $rootId);
        $ajaxName = '';
        if ('editAll' === $input->getValue('act')) {
            $ajaxKey  = (string) preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $ajaxKey);
            $ajaxName = (string) preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $name);
        }

        /** @psalm-suppress MixedAssignment */
        $nodes          = $session->get($ajaxKey);
        /** @psalm-suppress MixedArrayAssignment */
        $nodes[$ajaxId] = (int) $input->getValue('state');
        $session->set($ajaxKey, $nodes);

        $arrData = [
            'strTable' => $definition->getName(),
            'id'       => $ajaxName ?: $rootId,
            'name'     => $name,
        ];

        /**
         * @psalm-suppress UndefinedDocblockClass
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedArrayAccess
         * @var PageSelector $widget
         */
        $widget        = new $GLOBALS['BE_FFL']['pageSelector']($arrData, $this->getDataContainer());
        /** @psalm-suppress UndefinedClass */
        $widget->value = $this->getTreeValue('page', (string) $input->getValue('value'));

        /**
         * @psalm-suppress InvalidArgument - rather pass it "as is", we do not trust Contao annotations.
         * @psalm-suppress UndefinedDocblockClass
         * @psalm-suppress MixedArgument
         */
        $response = new Response($widget->generateAjax($ajaxId, (string) $field, $level));

        throw new ResponseException($response);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ResponseException Throws a response exception.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    #[\Override]
    protected function loadFiletree()
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        /** @psalm-suppress MixedAssignment */
        $folder = $input->getValue('folder');
        /** @psalm-suppress MixedAssignment */
        $field  = $input->getValue('field');
        $level  = (int) $input->getValue('level');

        /** @psalm-suppress MixedAssignment */
        $arrData             = [];
        /** @psalm-suppress MixedAssignment */
        $arrData['strTable'] = $input->getParameter('table');
        /** @psalm-suppress MixedAssignment */
        $arrData['id']       = $field;
        /** @psalm-suppress MixedAssignment */
        $arrData['name']     = $field;
        $arrData             = array_merge(
            $definition->getPropertiesDefinition()->getProperty((string) $field)->getExtra(),
            $arrData
        );

        /**
         * @psalm-suppress UndefinedClass
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedArrayAccess
         * @var FileSelector $widget
         */
        $widget = new $GLOBALS['BE_FFL']['fileSelector']($arrData, $this->getDataContainer());

        /** @psalm-suppress UndefinedClass */
        $widget->value = $this->getTreeValue((string) $field, (string) $input->getValue('value'));
        // Load a particular node.
        if ('' !== $folder) {
            /**
             * @psalm-suppress UndefinedDocblockClass
             * @psalm-suppress MixedArgument
             */
            $response = new Response($widget->generateAjax((string) $folder, (string) $field, $level));
        } else {
            /** @psalm-suppress UndefinedDocblockClass, MixedArgument */
            $response = new Response($widget->generate());
        }

        throw new ResponseException($response);
    }

    /**
     * Retrieve the value as serialized array.
     *
     * If the type is "file", the file names will automatically be added to the Dbafs and converted to file id.
     *
     * @param string $type  Either "page" or "file".
     * @param string $value The value as comma separated list.
     *
     * @return list<string> The value array.
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    protected function getTreeValue($type, $value)
    {
        // Convert the selected values.
        if ('' === $value) {
            return [];
        }
        $value = StringUtil::trimsplit("\t", $value);

        // Automatically add resources to the DBAFS.
        if ('file' === $type) {
            /** @psalm-suppress MixedAssignment */
            foreach ($value as $k => $v) {
                $uuid = Dbafs::addResource(urldecode((string) $v))->uuid;
                assert(is_string($uuid));
                $value[$k] = StringUtil::binToUuid($uuid);
            }
        }

        return array_values($value);
    }

    /**
     * Retrieve a model from a serialized id.
     *
     * Exits the script if no model has been found.
     *
     * @param string $serializedId The serialized id.
     *
     * @return ModelInterface
     *
     * @throws ResponseException Throws a response exception.
     */
    protected function getModelFromSerializedId($serializedId)
    {
        $modelId = ModelId::fromSerialized($serializedId);

        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        if (null === $model) {
            $definition = $environment->getDataDefinition();
            assert($definition instanceof ContainerInterface);

            $event = new LogEvent(
                'A record with the ID "' . $serializedId . '" does not exist in "' .
                $definition->getName() . '"',
                'Ajax executePostActions()',
                'ERROR'
            );

            $dispatcher = $environment->getEventDispatcher();
            assert($dispatcher instanceof EventDispatcherInterface);

            $dispatcher->dispatch($event, ContaoEvents::SYSTEM_LOG);

            throw new ResponseException(new Response(Response::$statusTexts[400], 400));
        }

        return $model;
    }

    /**
     * Reload the file tree.
     *
     * @return never
     *
     * @throws ResponseException Throws a response exception.
     */
    protected function reloadTree()
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        /** @psalm-suppress MixedAssignment */
        $serializedId = ($input->hasParameter('id') && $input->getParameter('id')) ? $input->getParameter('id') : null;
        /** @psalm-suppress MixedAssignment */
        $value        = $input->hasValue('value') ? $input->getValue('value', true) : '';

        $fieldName = $this->getFieldName();
        if (null === $fieldName) {
            throw new ResponseException(new Response('No update of the widget, as no field name was found.'));
        }

        /** @psalm-suppress MixedArgument */
        $widget = $this->getWidget($fieldName, null === $serializedId ? null : (string) $serializedId, (string) $value);
        assert($widget instanceof Widget);

        $this->generateWidget($widget);

        throw new ResponseException(new Response(''));
    }

    /**
     * {@inheritDoc}
     *
     * @return never
     */
    #[\Override]
    protected function reloadPagetree()
    {
        $this->reloadTree();
    }

    /**
     * {@inheritDoc}
     *
     * @return never
     */
    #[\Override]
    protected function reloadFiletree()
    {
        $this->reloadTree();
    }

    /**
     * {@inheritDoc}
     *
     * @throws ResponseException Throws a response exception.
     *
     * @return never
     */
    #[\Override]
    protected function setLegendState()
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        $session = $environment->getSessionStorage();
        assert($session instanceof SessionStorageInterface);

        /** @psalm-suppress MixedAssignment */
        $states = $session->get('LEGENDS');

        $table  = (string) $input->getValue('table');
        $legend = (string) $input->getValue('legend');
        /** @psalm-suppress MixedArrayAccess, MixedArrayOffset, MixedArrayAssignment */
        $states[$table][$legend] = (bool) $input->getValue('state');
        $session->set('LEGENDS', $states);

        throw new ResponseException(new Response(''));
    }

    /**
     * Get the field name.
     *
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getFieldName()
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        /** @psalm-suppress MixedAssignment */
        $fieldName = $inputProvider->hasValue('name') ? $inputProvider->getValue('name') : null;
        if (null === $fieldName) {
            return null;
        }

        if (('select' !== $inputProvider->getParameter('act')) && ('edit' !== $inputProvider->getParameter('mode'))) {
            /** @psalm-suppress MixedReturnStatement */
            return $fieldName;
        }

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        /** @psalm-suppress MixedOperand */
        $session = $sessionStorage->get($dataDefinition->getName() . '.' . $inputProvider->getParameter('select'));
        if (!is_array($session) || !isset($session['models'])) {
            return null;
        }
        /** @var array{models: list<string>} $session */

        $originalPropertyName = null;
        /** @psalm-suppress MixedAssignment */
        foreach ($session['models'] as $modelId) {
            if (null !== $originalPropertyName) {
                break;
            }
            $propertyNamePrefix = str_replace('::', '____', ((string) $modelId)) . '_';
            if (!str_starts_with((string) $fieldName, $propertyNamePrefix)) {
                continue;
            }

            $originalPropertyName = substr((string) $fieldName, strlen($propertyNamePrefix));
        }

        if (null === $originalPropertyName) {
            /** @psalm-suppress MixedReturnStatement */
            return $fieldName;
        }

        return $originalPropertyName;
    }

    /**
     * Generate the widget.
     *
     * @param Widget $widget The widget.
     *
     * @return void
     */
    private function generateWidget(Widget $widget)
    {
        $environment = $this->getEnvironment();
        assert($environment instanceof EnvironmentInterface);

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if (('select' !== $inputProvider->getParameter('act')) && ('edit' !== $inputProvider->getParameter('mode'))) {
            echo $widget->parse();

            return;
        }

        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        $model = $dataProvider->getEmptyModel();
        $model->setProperty($widget->name, $widget->value);

        /** @psalm-suppress MixedArgument */
        $widget = (new ContaoWidgetManager($environment, $model))->getWidget((string) $inputProvider->getValue('name'));
        assert($widget instanceof Widget);

        echo $widget->parse();
    }
}
