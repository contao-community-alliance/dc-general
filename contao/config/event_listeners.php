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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\ExtendedLegacyDcaDataDefinitionBuilder;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\BackendViewPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\DataProviderPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\ExtendedLegacyDcaPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\HardCodedPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\PickerCompatPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Subscriber\FormatModelLabelSubscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\CopyHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\CreateHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\DeleteHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\SelectHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ToggleHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\GetGroupHeaderSubscriber;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;

return array(
    // View related listeners
    DcGeneralEvents::FORMAT_MODEL_LABEL => array(
        array(new FormatModelLabelSubscriber(), 'handleFormatModelLabel'),
    ),
    BuildDataDefinitionEvent::NAME => array(
        array(
            array(new LegacyDcaDataDefinitionBuilder(), 'process'),
            LegacyDcaDataDefinitionBuilder::PRIORITY
        ),
        array(
            array(new ExtendedLegacyDcaDataDefinitionBuilder(), 'process'),
            ExtendedLegacyDcaDataDefinitionBuilder::PRIORITY
        ),
    ),
    PopulateEnvironmentEvent::NAME => array(
        array(
            array(new DataProviderPopulator(), 'process'),
            DataProviderPopulator::PRIORITY
        ),
        array(
            array(new ExtendedLegacyDcaPopulator(), 'process'),
            ExtendedLegacyDcaPopulator::PRIORITY
        ),
        array(
            array(new BackendViewPopulator(), 'process'),
            BackendViewPopulator::PRIORITY
        ),
        array(
            array(new HardCodedPopulator(), 'process'),
            HardCodedPopulator::PRIORITY
        ),
        array(
            array(new PickerCompatPopulator(), 'process'),
            PickerCompatPopulator::PRIORITY
        ),
    ),
    GetGroupHeaderEvent::NAME => array(
        array(new GetGroupHeaderSubscriber(), 'handle')
    ),
    DcGeneralEvents::ACTION => array(
        array(new CreateHandler(), 'handleEvent'),
        array(new SelectHandler(), 'handleEvent'),
        array(new CopyHandler(), 'handleEvent'),
        array(new DeleteHandler(), 'handleEvent'),
        array(new ToggleHandler(), 'handleEvent')
    ),
    BuildWidgetEvent::NAME => array(
        'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\WidgetBuilder::handleEvent'
    )
);
