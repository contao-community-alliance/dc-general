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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\ModelAwareInterface;

/**
 * Class GetOperationButtonsEvent.
 *
 * This event gets issued when all operation buttons of a row have been generated and holds the list of the rendered
 * buttons, keyed by command name. Listeners may reorder, remove or add entries before they are joined.
 *
 * The counterpart for the buttons above the listing is the GetGlobalButtonsEvent.
 *
 * @api
 */
class GetOperationButtonsEvent extends BaseGetButtonsEvent implements ModelAwareInterface
{
    public const string NAME = 'dc-general.view.contao2backend.get-operation-buttons';

    /**
     * The model the buttons belong to.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelInterface       $model       The model the buttons belong to.
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $model)
    {
        parent::__construct($environment);

        $this->model = $model;
    }

    /**
     * Retrieve the model the buttons belong to.
     *
     * @return ModelInterface
     */
    #[\Override]
    public function getModel()
    {
        return $this->model;
    }
}
