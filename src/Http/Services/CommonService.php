<?php

namespace Ifantace\Common\Http\Services;

use Illuminate\Http\Request;

class CommonService
{
    protected $response_array;
    protected $input;

    public function initInput(Request $input)
    {
        $this->input = $input;
    }
}
