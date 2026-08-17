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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Implementation of a "back" command.
 *
 * @api
 */
class BackCommand extends Command
{
    /**
     * Create a new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->extra['class']      = 'header_back dcg';
        $this->extra['accesskey']  = 'b';
        // Discard, not store, the way Contao renders its own back button. Contaos scroll offset
        // holds a single value that the next page consumes and clears, so it fits a listing that
        // renders anew - not a trip into a mask. Storing here would carry the position of the
        // mask over to the listing and jump to a place that means nothing there.
        $this->extra['attributes'] = 'data-action="contao--scroll-offset#discard"';
        $this
            ->setName('back_button')
            ->setLabel('backBT')
            ->setDescription('backBTTitle');
    }
}
