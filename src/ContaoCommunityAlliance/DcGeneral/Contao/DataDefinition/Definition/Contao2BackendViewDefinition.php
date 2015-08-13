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

namespace ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollection;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultListingConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultPanelLayout;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelLayoutInterface;

/**
 * Reference implementation for BasicDefinitionInterface.
 *
 * @package DcGeneral\DataDefinition\Definition
 */
class Contao2BackendViewDefinition implements Contao2BackendViewDefinitionInterface
{

    /**
     * The listing configuration for this backend view.
     *
     * @var ListingConfigInterface
     */
    protected $listingConfig;

    /**
     * The collection of global commands for this backend view.
     *
     * @var CommandCollectionInterface
     */
    protected $globalCommands;

    /**
     * The collection of commands invokable on a model for this backend view.
     *
     * @var CommandCollectionInterface
     */
    protected $modelCommands;

    /**
     * The current panel layout.
     *
     * @var PanelLayoutInterface
     */
    protected $panelLayout;

    /**
     * Create a new instance of the Contao2BackendViewDefinition.
     *
     * The sections will get initialized with instances of the default implementation.
     */
    public function __construct()
    {
        $this->listingConfig  = new DefaultListingConfig();
        $this->globalCommands = new CommandCollection();
        $this->modelCommands  = new CommandCollection();
        $this->panelLayout    = new DefaultPanelLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function getListingConfig()
    {
        return $this->listingConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalCommands()
    {
        return $this->globalCommands;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelCommands()
    {
        return $this->modelCommands;
    }

    /**
     * {@inheritdoc}
     */
    public function getPanelLayout()
    {
        return $this->panelLayout;
    }
}
