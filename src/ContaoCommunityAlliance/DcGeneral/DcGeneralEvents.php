<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
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
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

/**
 * This class holds everything together.
 */
class DcGeneralEvents
{
    /**
     * The ACTION event occurs when an action must be handled.
     *
     * This event allows to handle DC actions. The event listener method
     * receives a ContaoCommunityAlliance\DcGeneral\Event\ActionEvent
     * instance.
     *
     * @var string
     *
     * @api
     */
    const ACTION = 'dc-general.action';

    /**
     * The VIEW event occurs when a specific view must be rendered.
     *
     * This event allows you to render a specific view. The event listener method
     * receives a ContaoCommunityAlliance\DcGeneral\Event\ViewEvent instance.
     *
     * @var string
     *
     * @Event
     *
     * @api
     */
    const VIEW = 'dc-general.view';

    /**
     * The FORMAT_MODEL_LABEL event occurs when a label for a model must be formatted.
     *
     * The event listener method receives a ContaoCommunityAlliance\DcGeneral\Event\FormatModelLabelEvent instance.
     *
     * @var string
     *
     * @Event
     *
     * @api
     */
    const FORMAT_MODEL_LABEL = 'dc-general.model.format_model_label';

    /**
     * Triggered when a models relationship must be recalculated.
     *
     * @see ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent
     */
    const ENFORCE_MODEL_RELATIONSHIP = 'dc-general.model.enforce-relationship';
}
