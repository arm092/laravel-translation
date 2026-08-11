<?php

namespace Arm092\Translation\Console\Commands;

use Arm092\Translation\Drivers\Translation;
use Illuminate\Console\Command;

class BaseCommand extends Command
{
    protected $translation;

    public function __construct(Translation $translation)
    {
        parent::__construct();
        $this->translation = $translation;
    }
}
