<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use ContaoCommunityAlliance\DcGeneral\BaseConfigRegistry;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Clipboard;
use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use ContaoCommunityAlliance\DcGeneral\Controller\DefaultController;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;

/**
 * Class HardCodedPopulator.
 *
 * This class only exists to have some intermediate hardcoded transition point until the builder ans populators have
 * been properly coded. This class will then be removed from the code base.
 *
 * @deprecated Should get removed from the code base!!!!
 */
class HardCodedPopulator extends AbstractEventDrivenEnvironmentPopulator
{
    public const PRIORITY = -100;

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
        // @codingStandardsIgnoreStart
        @\trigger_error('Fallback populator in use - implement a proper populator!', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        $environment->setController((new DefaultController())->setEnvironment($environment));
    }

    /**
     * {@inheritDoc}
     */
    public function populate(EnvironmentInterface $environment)
    {
        if (!$environment->getSessionStorage()) {
            $sessionStorage = \System::getContainer()->get('cca.dc-general.session_factory')->createService();
            $sessionStorage->setScope('DC_GENERAL_' . \strtoupper($environment->getDataDefinition()->getName()));
            $environment->setSessionStorage($sessionStorage);
            // @codingStandardsIgnoreStart
            @\trigger_error('Fallback populator in use - implement a proper populator!', E_USER_DEPRECATED);
            // @codingStandardsIgnoreEnd
        }

        if (!$environment->getInputProvider()) {
            $environment->setInputProvider(new InputProvider());
            // @codingStandardsIgnoreStart
            @\trigger_error('Fallback populator in use - implement a proper populator!', E_USER_DEPRECATED);
            // @codingStandardsIgnoreEnd
        }

        if (!$environment->getClipboard()) {
            $environment->setClipboard(new Clipboard());
            // @codingStandardsIgnoreStart
            @\trigger_error('Fallback populator in use - implement a proper populator!', E_USER_DEPRECATED);
            // @codingStandardsIgnoreEnd
        }

        if (!$environment->getBaseConfigRegistry()) {
            $baseConfigRegistry = new BaseConfigRegistry();
            $baseConfigRegistry->setEnvironment($environment);
            $environment->setBaseConfigRegistry($baseConfigRegistry);
            // @codingStandardsIgnoreStart
            @\trigger_error('Fallback populator in use - implement a proper populator!', E_USER_DEPRECATED);
            // @codingStandardsIgnoreEnd
        }

        $this->populateController($environment);
    }
}
