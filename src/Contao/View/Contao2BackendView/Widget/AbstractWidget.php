<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget;

use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Abstract widget class as base for dc general backend widgets.
 *
 * This widget is only prepared to run in DcCompat mode!
 */
abstract class AbstractWidget extends Widget
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
     * @param array|null    $attributes    The custom attributes.
     * @param DcCompat|null $dataContainer The data container.
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
