<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
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
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Implementation of a "new" command.
 */
class CreateModelCommand extends Command
{
    /**
     * Create a new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->extra['class']      = 'header_new';
        $this->extra['accesskey']  = 'n';
        $this->extra['attributes'] = 'onclick="Backend.getScrollOffset();"';
        $this
            ->setName('button_new')
            ->setLabel('new.label')
            ->setDescription('new.description');
        $this->parameters['act'] = 'create';
    }
}
