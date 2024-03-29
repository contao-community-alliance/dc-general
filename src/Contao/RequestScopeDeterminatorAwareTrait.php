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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao;

/**
 * This trait eases the current scope determination.
 */
trait RequestScopeDeterminatorAwareTrait
{
    /**
     * The request mode determinator.
     *
     * @var null|RequestScopeDeterminator
     */
    private ?RequestScopeDeterminator $scopeDeterminator = null;

    /**
     * ClipboardController constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request scope determinator.
     *
     * @return void
     */
    public function setScopeDeterminator(RequestScopeDeterminator $scopeDeterminator): void
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    private function getScopeDeterminator(): RequestScopeDeterminator
    {
        if (null === $this->scopeDeterminator) {
            throw new \RuntimeException('scopeDeterminator has not been set.');
        }

        return $this->scopeDeterminator;
    }
}
