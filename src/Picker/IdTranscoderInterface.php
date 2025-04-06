<?php

namespace ContaoCommunityAlliance\DcGeneral\Picker;

interface IdTranscoderInterface
{
    public function encode(string $id): string;
    public function decode(string $encodedId): string;
}
