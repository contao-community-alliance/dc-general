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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use ContaoCommunityAlliance\DcGeneral\Clipboard\DefaultClipboard;
use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use ContaoCommunityAlliance\DcGeneral\Controller\DefaultController;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

/**
 * Class HardCodedPopulator.
 *
 * This class only exists to have some intermediate hardcoded transition point until the builder ans populators have
 * been properly coded. This class will then be removed from the code base.
 *
 * @package DcGeneral\Contao\Dca\Populator
 */
class HardCodedPopulator extends AbstractEventDrivenEnvironmentPopulator
{
    const PRIORITY = 1000;

    /**
     * Create a controller instance in the environment if none has been defined yet.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     *
     * @internal
     */
    public function populateController(EnvironmentInterface $environment)
    {
        // Already populated, get out then.
        if ($environment->getController()) {
            return;
        }

        $controller = new DefaultController();

        $controller->setEnvironment($environment);
        $environment->setController($controller);
    }

    /**
     * {@inheritDoc}
     */
    public function populate(EnvironmentInterface $environment)
    {
        if (!$environment->getInputProvider()) {
            $environment->setInputProvider(new InputProvider());
        }

        if (!$environment->getClipboard()) {
            $environment->setClipboard(new DefaultClipboard());
        }

        $this->populateController($environment);
    }
}
