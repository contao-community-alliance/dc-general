<?php

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Picker;

use Contao\CoreBundle\Picker\PickerConfig;

interface IdTranscodingPickerProviderInterface
{
    public function createIdTranscoder(PickerConfig $config): IdTranscoderInterface;
}
