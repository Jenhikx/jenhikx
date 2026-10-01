@extends('layouts.app')

@section('title', 'Manage Access — JENHIKX')

@section('page-title')
    Control what each <span class="jx-chip lime">@include('partials.icon', ['name' => 'access'])</span> user<br>
    can see
@endsection

@section('content')

    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-2xl bg-red-50 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <p class="text-sm text-[var(--muted)] mb-5">
        Admin accounts always have full access. This page only controls what "User" role accounts can see —
        Ad Spend, P&amp;L, and Notes.
    </p>

    @if ($users->isEmpty())

        <div class="jx-card text-center py-12">
            <p class="text-lg font-medium">No user accounts yet</p>
            <p class="text-sm text-[var(--muted)] mt-1">Access control only applies to accounts with the "User" role.</p>
            <a href="{{ route('users.index') }}" class="jx-btn jx-btn-dark mt-5">Go to Manage Users</a>
        </div>

    @else

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($users as $user)
                @php $granted = $user->moduleAccess->pluck('module')->toArray(); @endphp

                <div class="jx-card">
                    <p class="font-semibold">{{ $user->name }}</p>
                    <p class="text-xs text-[var(--muted)] mb-4">{{ $user->email }}</p>

                    <form action="{{ route('access.update', $user) }}" method="POST" class="space-y-2">
                        @csrf
                        @method('PUT')

                        @foreach ($modules as $module)
                            <label class="flex items-center gap-2 text-sm bg-white border border-[var(--line)] rounded-xl px-3 py-2 cursor-pointer">
                                <input type="checkbox" name="modules[]" value="{{ $module }}"
                                       @checked(in_array($module, $granted))
                                       class="rounded border-[var(--line)]">
                                @if ($module === 'ad-spend')
                                    Ad Spend
                                @elseif ($module === 'pnl')
                                    P&amp;L
                                @else
                                    Notes
                                @endif
                            </label>
                        @endforeach

                        <button type="submit" class="jx-btn jx-btn-dark w-full mt-3" style="height:40px;">Save Access</button>
                    </form>
                </div>
            @endforeach
        </div>

    @endif

@endsection