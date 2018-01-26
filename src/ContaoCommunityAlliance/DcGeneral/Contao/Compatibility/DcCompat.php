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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Compatibility;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DC_General;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Small compatibility layer for callbacks, that expect a "full featured" DC instance.
 */
class DcCompat extends DC_General
{
    /**
     * The current model.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * Name of the property currently working on.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment  The Dc instance to use for delegating.
     *
     * @param ModelInterface       $model        The model within scope (optional).
     *
     * @param null                 $propertyName The name of the property within scope (optional).
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $model = null, $propertyName = null)
    {
        // Prevent "Recoverable error: Argument X passed to SomClass::someMethod() must be an instance of DataContainer,
        // instance of ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat given" in callbacks.
        if (!class_exists('\DataContainer', false)) {
            class_alias('\Contao\DataContainer', '\DataContainer');
        }
        $this->objEnvironment = $environment;
        $this->model          = $model;
        $this->propertyName   = $propertyName;
    }

    /**
     * Retrieve the current model.
     *
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Retrieve the current property.
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralException This method is for internal use only.
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
    protected function getTablenameCallback($strTable)
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
                    return $this->getModel()->getId();
                }

                $environment    = $this->getEnvironment();
                $dataDefinition = $environment->getDataDefinition();
                $inputProvider  = $environment->getInputProvider();

                $idParameter = $inputProvider->hasParameter('id') ? 'id' : 'pid';
                if (!$inputProvider->hasParameter($idParameter)) {
                    return null;
                }

                $modelId = ModelId::fromSerialized($inputProvider->getParameter($idParameter));
                if ($modelId->getDataProviderName() === $dataDefinition->getName()) {
                    return $modelId->getId();
                }

                if (('pid' === $idParameter)
                    || !$inputProvider->hasParameter('pid')
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
                    return $this->getEnvironment()->getParentDataDefinition()->getName();
                }
                return null;

            case 'childTable':
                throw new DcGeneralRuntimeException('The magic property $dc->childTable is not supported yet!');

            case 'rootIds':
                throw new DcGeneralRuntimeException('The magic property $dc->rootIds is not supported yet!');

            case 'createNewVersion':
                throw new DcGeneralRuntimeException('The magic property $dc->createNewVersion is not supported yet!');

            case 'table':
                return $this->getEnvironment()->getDataProvider()->getEmptyModel()->getProviderName();

            case 'value':
                if ($this->propertyName && $this->getModel()) {
                    return $this->getModel()->getProperty($this->propertyName);
                }
                return null;

            case 'field':
                return $this->propertyName;

            case 'inputName':
                return $this->propertyName;

            case 'palette':
                throw new DcGeneralRuntimeException('The magic property $dc->palette is not supported yet!');

            case 'activeRecord':
                return new ActiveRecord($this->model);

            default:
        }

        throw new DcGeneralRuntimeException('The magic property ' . $name . ' is not supported (yet)!');
    }
}
