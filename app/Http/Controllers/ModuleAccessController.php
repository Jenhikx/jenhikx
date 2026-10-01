<?php

namespace App\Http\Controllers;

use App\Models\ModuleAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleAccessController extends Controller
{
    public function index()
    {
        // Sirf "user" role walon ka access control karna hai, admin ko sab milta hai already
        $users = User::where('role', 'user')
            ->with('moduleAccess')
            ->orderBy('name')
            ->get();

        $modules = User::MODULES;

        return view('access.index', compact('users', 'modules'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'user') {
            return back()->with('error', 'Access control only applies to user accounts.');
        }

        $request->validate([
            'modules' => ['array'],
            'modules.*' => [Rule::in(User::MODULES)],
        ]);

        $selected = $request->input('modules', []);

        // Purana access hata ke naya set save karo — checkbox jo checked hain wahi rahenge
        ModuleAccess::where('user_id', $user->id)->delete();

        foreach ($selected as $module) {
            ModuleAccess::create(['user_id' => $user->id, 'module' => $module]);
        }

        return back()->with('success', $user->name . "'s access updated.");
    }
}