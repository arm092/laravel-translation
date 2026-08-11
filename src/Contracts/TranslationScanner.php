<?php

namespace Arm092\Translation\Contracts;

use Arm092\Translation\Scanning\ScanResult;

interface TranslationScanner
{
    public function scan(bool $refresh = false): ScanResult;
}
