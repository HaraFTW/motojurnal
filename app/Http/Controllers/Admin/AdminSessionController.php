<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSessionController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (AdminAuth::isUnlocked($request)) {
            return redirect()->route('admin.files.index');
        }

        return view('admin.unlock');
    }

    public function store(Request $request): View|RedirectResponse
    {
        if (! AdminAuth::passwordMatches($request->input('password'))) {
            return view('admin.unlock');
        }

        AdminAuth::unlock($request);

        return redirect()->route('admin.files.index');
    }
}
