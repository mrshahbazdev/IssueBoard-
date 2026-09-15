<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $supportedLocales = array_keys(config('app.supported_locales'));

        $data = $request->validate([
            'locale' => ['required', 'string', Rule::in($supportedLocales)],
        ]);

        $request->session()->put('locale', $data['locale']);

        return back();
    }
}
