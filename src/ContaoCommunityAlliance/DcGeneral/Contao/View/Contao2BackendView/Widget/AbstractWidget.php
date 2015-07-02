<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Abstract widget class as base for dc general backend widgets.
 *
 * This widget is only prepared to run in DcCompat mode!
 */
abstract class AbstractWidget extends \Widget
{
    /**
     * Submit user input.
     *
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * The template.
     *
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * The data Container.
     *
     * @var DcCompat
     */
    protected $dataContainer;

    /**
     * Create a new instance.
     *
     * @param array|null      $attributes    The custom attributes.
     *
     * @param DcCompat|null   $dataContainer The data container.
     */
    public function __construct($attributes = null, DcCompat $dataContainer = null)
    {
        parent::__construct($attributes);

        $this->dataContainer = $dataContainer ?: $this->objDca;
    }

    /**
     * Get the environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->dataContainer->getEnvironment();
    }

    /**
     * Get the model.
     *
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->dataContainer->getModel();
    }
}
