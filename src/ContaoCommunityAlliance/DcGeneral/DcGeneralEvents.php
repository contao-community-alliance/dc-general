<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

/**
 * This class holds everything together.
 *
 * @package DcGeneral
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
}
