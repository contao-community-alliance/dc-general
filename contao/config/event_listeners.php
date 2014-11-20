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

use ContaoCommunityAlliance\DcGeneral\Contao\Subscriber\FormatModelLabelSubscriber;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;

return array(
    // View related listeners
    DcGeneralEvents::FORMAT_MODEL_LABEL => array(
        array(new FormatModelLabelSubscriber(), 'handleFormatModelLabel'),
    ),
);
