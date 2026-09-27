<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'moduleCount' => Module::count(),
            'lessonCount' => Lesson::count(),
            'userCount' => User::count(),
            'adminCount' => User::where('role', 'admin')->count(),
            'modules' => Module::withCount('lessons')->orderBy('sort_order')->get(),
        ]);
    }
}
