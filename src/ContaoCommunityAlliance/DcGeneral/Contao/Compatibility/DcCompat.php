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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Compatibility;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DC_General;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * Class DcCompat
 *
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
     */
    public function __get($name)
    {
        switch ($name) {
            case 'id':
                return $this->getModel() ? $this->getModel()->getId() : null;

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
