<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\DeleteHandler;
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
        array(new DeleteHandler(), 'handleEvent')
    ),
);
