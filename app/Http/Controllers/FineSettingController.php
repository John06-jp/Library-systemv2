<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FineSettingController extends Controller
{
    public function edit()
    {
        return redirect()->route('circulation.policy.edit');
    }

    public function update(Request $request)
    {
        return redirect()->route('circulation.policy.edit');
    }
}
