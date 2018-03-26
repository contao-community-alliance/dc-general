<?php

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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Picker;

use Contao\CoreBundle\Picker\PagePickerProvider as ContaoPagePickerProvider;

/**
 * Provides the page picker.
 */
class PagePickerProvider extends ContaoPagePickerProvider
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ccaPagePicker';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        return \in_array($context, ['cca_ page', 'cca_link'], true) && $this->getUser()->hasAccess('page', 'modules');
    }
}
