@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-users me-2" style="color:var(--admin-primary)"></i>Users</h5>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        @if ($users->count() > 0)
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Avatar</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>
                        <img class="admin-user-thumb" src="{{ Gravatar::get($user->email) }}" alt="{{ $user->name }}">
                    </td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-{{ $user->isAdmin() ? 'success' : 'secondary' }}">{{ ucfirst($user->role) }}</span></td>
                    <td class="text-end">
                        @if ($user->id !== auth()->id())
                            @if ($user->isAdmin())
                            <form action="{{ route('users.make-customer', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-admin-sm btn-admin-edit">
                                    <i class="fas fa-user me-1"></i> Make Customer
                                </button>
                            </form>
                            @else
                            <form action="{{ route('users.make-admin', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-admin-sm btn-admin-restore">
                                    <i class="fas fa-shield-halved me-1"></i> Make Admin
                                </button>
                            </form>
                            @endif
                        @else
                            <small class="text-muted">You</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="admin-empty">
            <i class="fas fa-users"></i>
            <h5>No Users Yet</h5>
        </div>
        @endif
    </div>
</div>
@endsection