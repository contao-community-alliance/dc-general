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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Compatibility;

use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DC\General;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

/**
 * Small compatibility layer for callbacks, that expect a "full-featured" DC instance.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DcCompat extends General
{
    /**
     * The current model.
     *
     * @var ModelInterface|null
     */
    protected $model;

    /**
     * Name of the property currently working on.
     *
     * @var string|null
     */
    protected $propertyName;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment  The Dc instance to use for delegating.
     * @param ModelInterface|null  $model        The model within scope (optional).
     * @param string|null          $propertyName The name of the property within scope (optional).
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $model = null, $propertyName = null)
    {
        // Prevent "Recoverable error: Argument X passed to SomClass::someMethod() must be an instance of DataContainer,
        // instance of ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat given" in callbacks.
        if (!\class_exists('\DataContainer', false)) {
            \class_alias('\Contao\DataContainer', '\DataContainer');
        }
        $this->objEnvironment = $environment;
        $this->model          = $model;
        $this->propertyName   = $propertyName;
    }

    /**
     * Retrieve the current model.
     *
     * @return ModelInterface|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Retrieve the current property.
     *
     * @return string|null
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Internal use only.
     *
     * @throws DcGeneralException This method is for internal use only.
     *
     * @return never
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handlePopulateEnvironment(PopulateEnvironmentEvent $event)
    {
        throw new DcGeneralException(
            __CLASS__ . '::handlePopulateEnvironment() is internal use only and must not be called'
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralException This method is for internal use only.
     */
    protected function getTablenameCallback($tableName)
    {
        throw new DcGeneralException(
            __CLASS__ . '::getTablenameCallback() is internal use only and must not be called'
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException The magic setter is unsupported and has been deactivated.
     */
    public function __set($strKey, $varValue)
    {
        throw new DcGeneralRuntimeException('The magic setter is not supported anymore!');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralRuntimeException When an unknown key is encountered.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __get($name)
    {
        switch ($name) {
            case 'id':
                if (null !== $this->getModel()) {
                    $model = $this->getModel();
                    assert($model instanceof ModelInterface);
                    return $model->getId();
                }

                $environment = $this->getEnvironment();

                $dataDefinition = $environment->getDataDefinition();
                assert($dataDefinition instanceof ContainerInterface);

                $inputProvider = $environment->getInputProvider();
                assert($inputProvider instanceof InputProviderInterface);

                $idParameter = $inputProvider->hasParameter('id') ? 'id' : 'pid';
                if (!$inputProvider->hasParameter($idParameter)) {
                    return null;
                }

                $modelId = ModelId::fromSerialized($inputProvider->getParameter($idParameter));
                if ($modelId->getDataProviderName() === $dataDefinition->getName()) {
                    return $modelId->getId();
                }

                if (
                    ('pid' === $idParameter)
                    || !$inputProvider->hasParameter('pid')
                    || !$inputProvider->getParameter('pid')
                ) {
                    return null;
                }

                $parentModelId = ModelId::fromSerialized($inputProvider->getParameter('pid'));
                if ($dataDefinition->getName() !== $parentModelId->getDataProviderName()) {
                    return null;
                }

                return $parentModelId->getId();

            case 'parentTable':
                if ($this->getEnvironment()->getParentDataDefinition()) {
                    $container = $this->getEnvironment()->getParentDataDefinition();
                    assert($container instanceof ContainerInterface);

                    return $container->getName();
                }
                return null;

            case 'childTable':
                throw new DcGeneralRuntimeException('The magic property $dc->childTable is not supported yet!');

            case 'rootIds':
                throw new DcGeneralRuntimeException('The magic property $dc->rootIds is not supported yet!');

            case 'createNewVersion':
                throw new DcGeneralRuntimeException('The magic property $dc->createNewVersion is not supported yet!');

            case 'table':
                $dataProvider = $this->getEnvironment()->getDataProvider();
                assert($dataProvider instanceof DataProviderInterface);

                return $dataProvider->getEmptyModel()->getProviderName();

            case 'value':
                if (null !== $this->propertyName && null !== $this->getModel()) {
                    $model = $this->getModel();
                    assert($model instanceof ModelInterface);

                    return $model->getProperty($this->propertyName);
                }
                return null;

            case 'field':
                return $this->propertyName;

            case 'inputName':
                return $this->propertyName;

            case 'palette':
                throw new DcGeneralRuntimeException('The magic property $dc->palette is not supported yet!');

            case 'activeRecord':
                assert($this->model instanceof ModelInterface);
                return new ActiveRecord($this->model);

            default:
        }

        throw new DcGeneralRuntimeException('The magic property ' . $name . ' is not supported (yet)!');
    }
}
