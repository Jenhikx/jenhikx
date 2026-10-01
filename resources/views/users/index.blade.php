@extends('layouts.app')

@section('title', 'Manage Users — JENHIKX')

@section('page-title')
    Manage your <span class="jx-chip lime">@include('partials.icon', ['name' => 'users'])</span> team<br>
    and their access
@endsection

@section('content')

    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-2xl bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-2xl bg-red-50 text-red-700 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Add user --}}
    <div class="jx-card mb-5">
        <p class="text-sm font-semibold mb-4">Add a user</p>
        <form action="{{ route('users.store') }}" method="POST" class="grid gap-3 md:grid-cols-5 items-end">
            @csrf
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Name</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Password</label>
                <input type="password" name="password" required class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Role</label>
                <select name="role" required class="jx-input w-full">
                    <option value="user" @selected(old('role') === 'user')>User</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </select>
            </div>
            <div>
                <button type="submit" class="jx-btn jx-btn-dark w-full">Add User</button>
            </div>
        </form>
    </div>

    {{-- List --}}
    <div class="bg-white border border-[var(--line)] rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="jx-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="r">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr id="view-{{ $user->id }}">
                            <td class="font-medium">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="jx-badge ml-2">You</span>
                                @endif
                            </td>
                            <td class="text-[var(--muted)]">{{ $user->email }}</td>
                            <td class="capitalize">{{ $user->role }}</td>
                            <td class="r whitespace-nowrap">
                                <button type="button" onclick="toggleEdit({{ $user->id }})" class="text-sm font-medium mr-4">Edit</button>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete this user?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        <tr id="edit-{{ $user->id }}" style="display:none;">
                            <td colspan="4" style="background:var(--surface);">
                                <form action="{{ route('users.update', $user) }}" method="POST" class="grid gap-2 md:grid-cols-5 items-end py-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $user->name }}" required class="jx-input w-full">
                                    <input type="email" name="email" value="{{ $user->email }}" required class="jx-input w-full">
                                    <input type="password" name="password" placeholder="Leave blank to keep" class="jx-input w-full">
                                    <select name="role" required class="jx-input w-full" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                        <option value="user" @selected($user->role === 'user')>User</option>
                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    </select>
                                    <div class="flex gap-2">
                                        <button type="submit" class="jx-btn jx-btn-dark flex-1" style="height:44px;">Save</button>
                                        <button type="button" onclick="toggleEdit({{ $user->id }})" class="jx-btn jx-btn-round" style="width:auto;padding:0 18px;height:44px;">Cancel</button>
                                    </div>
                                </form>
                                @if ($user->id === auth()->id())
                                    <p class="text-xs text-[var(--muted)] pb-2">You can't change your own role.</p>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleEdit(id) {
            var v = document.getElementById('view-' + id), e = document.getElementById('edit-' + id);
            var editing = e.style.display !== 'none';
            e.style.display = editing ? 'none' : '';
            v.style.display = editing ? '' : 'none';
        }
    </script>
@endsection