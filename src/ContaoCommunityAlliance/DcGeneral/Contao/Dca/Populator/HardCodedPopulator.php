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
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistry;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Clipboard;
use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use ContaoCommunityAlliance\DcGeneral\Contao\SessionStorage;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;
use ContaoCommunityAlliance\DcGeneral\Controller\DefaultController;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;

/**
 * Class HardCodedPopulator.
 *
 * This class only exists to have some intermediate hardcoded transition point until the builder ans populators have
 * been properly coded. This class will then be removed from the code base.
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
        if (!$environment->getSessionStorage()) {
            $environment->setSessionStorage(
                new SessionStorage('DC_GENERAL_' . strtoupper($environment->getDataDefinition()->getName()))
            );
        }

        if (!$environment->getInputProvider()) {
            $environment->setInputProvider(new InputProvider());
        }

        if (!$environment->getClipboard()) {
            $environment->setClipboard(new Clipboard());
        }

        if (!$environment->getBaseConfigRegistry()) {
            $baseConfigRegistry = new BaseConfigRegistry();
            $baseConfigRegistry->setEnvironment($environment);
            $environment->setBaseConfigRegistry($baseConfigRegistry);
        }

        $this->populateController($environment);
    }
}
