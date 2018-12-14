<?php

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener\SelectModeButtonsListener;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\ColorPickerWizardSubscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\WidgetBuilder;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\GetGroupHeaderSubscriber;

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\ExtendedLegacyDcaDataDefinitionBuilder;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Builder\Legacy\LegacyDcaDataDefinitionBuilder;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\BackendViewPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\DataProviderPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\ExtendedLegacyDcaPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\HardCodedPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator\ParentDefinitionPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\Subscriber\FormatModelLabelSubscriber;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\CopyHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\CreateHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\DeleteHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\EditAllHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\EditHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ListViewShowAllHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ListViewShowAllPropertiesHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\OverrideAllHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ParentedListViewShowAllHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\PasteAllHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\PasteHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\SelectHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\ToggleHandler;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGroupHeaderEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener\BackButtonListener;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener\CreateModelButtonListener;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\ParentEnforcingListener;
use ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship\TreeEnforcingListener;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;

$result = [
    BuildDataDefinitionEvent::NAME => [
        [
            [new LegacyDcaDataDefinitionBuilder(), 'process'],
            LegacyDcaDataDefinitionBuilder::PRIORITY
        ],
        [
            [new ExtendedLegacyDcaDataDefinitionBuilder(), 'process'],
            ExtendedLegacyDcaDataDefinitionBuilder::PRIORITY
        ],
    ],
    PopulateEnvironmentEvent::NAME => [
        [
            [new ParentDefinitionPopulator(), 'process'],
            ParentDefinitionPopulator::PRIORITY
        ],
        [
            [new DataProviderPopulator(), 'process'],
            DataProviderPopulator::PRIORITY
        ],
        [
            [new HardCodedPopulator(), 'process'],
            HardCodedPopulator::PRIORITY
        ],
    ],
    DcGeneralEvents::ENFORCE_MODEL_RELATIONSHIP => [
        [new TreeEnforcingListener(), 'process'],
        [new ParentEnforcingListener(), 'process'],
    ],
    GetSelectModeButtonsEvent::NAME => [
        [new SelectModeButtonsListener(), 'handleEvent']
    ]
];

if ('BE' === TL_MODE) {
    $result[PopulateEnvironmentEvent::NAME] = \array_merge(
        $result[PopulateEnvironmentEvent::NAME],
        [
            [
                [new ExtendedLegacyDcaPopulator(), 'process'],
                ExtendedLegacyDcaPopulator::PRIORITY
            ],
            [
                [new BackendViewPopulator(), 'process'],
                BackendViewPopulator::PRIORITY
            ]
        ]
    );

    $result[DcGeneralEvents::FORMAT_MODEL_LABEL] = [
        [new FormatModelLabelSubscriber(), 'handleFormatModelLabel'],
    ];

    $result[GetGroupHeaderEvent::NAME] = [
        [GetGroupHeaderSubscriber::class, 'handle']
    ];

    $result[BuildWidgetEvent::NAME] = [
        [WidgetBuilder::class, 'handleEvent'],
        [ColorPickerWizardSubscriber::class, 'handleEvent']
    ];

    $result[DcGeneralEvents::ACTION] = [
        [new ListViewShowAllPropertiesHandler(), 'handleEvent'],
        [new EditAllHandler(), 'handleEvent'],
        [new OverrideAllHandler(), 'handleEvent'],
        [new PasteAllHandler(), 'handleEvent'],
        [new SelectHandler(), 'handleEvent'],
        [new PasteHandler(), 'handleEvent'],
        [new CreateHandler(), 'handleEvent'],
        [new EditHandler(), 'handleEvent'],
        [new CopyHandler(), 'handleEvent'],
        [new DeleteHandler(), 'handleEvent'],
        [new ToggleHandler(), 'handleEvent'],
        [new ListViewShowAllHandler(), 'handleEvent'],
        [new ParentedListViewShowAllHandler(), 'handleEvent'],
    ];
    $result[GetGlobalButtonEvent::NAME] = [
        [new BackButtonListener(), 'handle'],
        [new CreateModelButtonListener(), 'handle'],
    ];
}

return $result;
