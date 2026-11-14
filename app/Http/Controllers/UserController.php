<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AvatarStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', ['title' => 'User Accounts', 'users' => User::query()->orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('users.form', ['title' => 'Create User', 'user' => new User]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->safe()->only(['username', 'full_name']));

        return to_route('users.index')->with('success', 'User created successfully. You can add a profile picture using Edit.');
    }

    public function edit(User $user): View
    {
        return view('users.form', ['title' => 'Edit User', 'user' => $user]);
    }

    public function update(UserRequest $request, User $user, AvatarStorage $avatars): RedirectResponse
    {
        $data = $request->safe()->only(['username', 'full_name']);
        $oldAvatar = $user->avatar;
        $newAvatar = null;
        if ($request->hasFile('avatar')) {
            $newAvatar = $avatars->store($request->file('avatar'));
            $data['avatar'] = $newAvatar;
        }
        try {
            $user->update($data);
        } catch (Throwable $exception) {
            if ($newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            } throw $exception;
        }
        if ($newAvatar && $oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return to_route('users.index')->with('success', 'User updated successfully.');
    }
}
