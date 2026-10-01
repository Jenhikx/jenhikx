@extends('layouts.app')

@section('title', 'Notes — JENHIKX')

@section('page-title')
    Keep your <span class="jx-chip lime">@include('partials.icon', ['name' => 'notes'])</span> team notes<br>
    in one place
@endsection

@section('content')

    {{-- Month filter --}}
    <form method="GET" action="{{ route('notes.index') }}" class="mb-5">
        <input type="month" name="month" value="{{ $month }}" class="jx-input" onchange="this.form.submit()">
    </form>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-2xl bg-red-50 text-red-700 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Add note --}}
    <div class="jx-card mb-5">
        <p class="text-sm font-semibold mb-4">Add a note</p>
        <form action="{{ route('notes.store') }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid gap-3 md:grid-cols-2">
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Title (optional)" class="jx-input w-full">
                <input type="date" name="date" required value="{{ old('date', now()->toDateString()) }}" class="jx-input w-full">
            </div>
            <textarea name="content" rows="3" required placeholder="Write your note here..."
                      class="jx-input w-full" style="height:auto;padding:12px 14px;">{{ old('content') }}</textarea>
            <button type="submit" class="jx-btn jx-btn-dark">Add Note</button>
        </form>
    </div>

    {{-- Notes grid --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($notes as $note)
            <div class="jx-card {{ $note->is_pinned ? 'lime' : '' }}" id="view-{{ $note->id }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <p class="font-semibold">{{ $note->title ?: 'Untitled' }}</p>
                    @if ($note->is_pinned)
                        <span class="jx-badge" style="background:#fff;">Pinned</span>
                    @endif
                </div>
                <p class="text-xs text-[var(--muted)] mb-2">{{ $note->date->format('d M Y') }}</p>
                <p class="text-sm whitespace-pre-line">{{ $note->content }}</p>
                <p class="text-xs text-[var(--muted)] mt-4">
                    {{ $note->creator->name ?? 'Unknown' }} · added {{ $note->created_at->format('d M Y, h:i A') }}
                </p>
                <div class="flex items-center gap-4 mt-3">
                    <button type="button" onclick="toggleEdit({{ $note->id }})" class="text-sm font-medium">Edit</button>
                    <form action="{{ route('notes.pin', $note) }}" method="POST" class="inline">
                        @csrf @method('PUT')
                        <button type="submit" class="text-sm font-medium">{{ $note->is_pinned ? 'Unpin' : 'Pin' }}</button>
                    </form>
                    <form action="{{ route('notes.destroy', $note) }}" method="POST" class="inline"
                          onsubmit="return confirm('Delete this note?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm font-medium text-red-600">Delete</button>
                    </form>
                </div>
            </div>

            <div class="jx-card" id="edit-{{ $note->id }}" style="display:none;">
                <form action="{{ route('notes.update', $note) }}" method="POST" class="space-y-3">
                    @csrf @method('PUT')
                    <div class="grid gap-3 md:grid-cols-2">
                        <input type="text" name="title" value="{{ $note->title }}" placeholder="Title (optional)" class="jx-input w-full">
                        <input type="date" name="date" value="{{ $note->date->format('Y-m-d') }}" required class="jx-input w-full">
                    </div>
                    <textarea name="content" rows="4" required class="jx-input w-full" style="height:auto;padding:12px 14px;">{{ $note->content }}</textarea>
                    <div class="flex gap-2">
                        <button type="submit" class="jx-btn jx-btn-dark" style="height:40px;">Save</button>
                        <button type="button" onclick="toggleEdit({{ $note->id }})" class="jx-btn jx-btn-round" style="width:auto;padding:0 16px;height:40px;">Cancel</button>
                    </div>
                </form>
            </div>
        @empty
            <div class="jx-card text-center py-10 sm:col-span-2 lg:col-span-3">
                <p class="text-[var(--muted)]">No notes for this month yet. Add your first one above.</p>
            </div>
        @endforelse
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