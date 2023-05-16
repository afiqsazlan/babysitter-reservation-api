<?php

namespace App\Actions;

use Illuminate\Support\Str;

class GenerateReferenceNumber
{
    public function execute()
    {
        return 'REF_' . Str::random(6);
    }
}
