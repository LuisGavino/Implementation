<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UpdateProfileRequest;
use App\User;

class UsersController extends Controller
{
    public function index()
    {
        return view('users.index')->with('users', User::all());
    }

    public function edit()
    {
        return view('users.edit')->with('user', auth()->user());
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $user->update([
            'name' => $request->name,
            'about' => $request->about,
        ]);

        session()->flash('success', 'User profile updated successfully');

        return redirect()->back();
    }

    public function makeAdmin(User $user)
    {
        $this->authorize('makeAdmin', $user);

        $user->role = User::ROLE_ADMIN;

        $user->save();

        session()->flash('success', 'user made admin successfully');

        return redirect(route('users.index'));

    }

    public function makeCustomer(User $user)
    {
        $this->authorize('makeCustomer', $user);

        $user->role = User::ROLE_CUSTOMER;

        $user->save();

        session()->flash('success', 'user role changed to customer successfully');

        return redirect(route('users.index'));
    }
}
