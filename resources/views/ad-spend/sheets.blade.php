@extends('layouts.app')

@section('title', 'Manage Sheets — JENHIKX')
@section('page-title', 'Manage sheets')

@section('header-actions')
    <a href="{{ route('ad-spend') }}" class="jx-btn jx-btn-round" style="width:auto;padding:0 20px;">Back to Ad Spend</a>
@endsection

@section('content')

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-2xl bg-red-50 text-red-700 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Add sheet --}}
    <div class="jx-card mb-4">
        <p class="text-sm font-semibold mb-4">Add a sheet</p>
        <form action="{{ route('ad-spend.sheets.store') }}" method="POST" class="grid gap-3 md:grid-cols-[1fr_1fr_auto] items-end">
            @csrf
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Store name</label>
                <input type="text" name="name" required class="jx-input w-full" placeholder="e.g. ApnaChoice">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Product name</label>
                <input type="text" name="product_name" required class="jx-input w-full" placeholder="e.g. Magic Handwriting Workbook">
            </div>
            <button type="submit" class="jx-btn jx-btn-dark">Add sheet</button>
        </form>
    </div>

    {{-- List --}}
    <div class="bg-white border border-[var(--line)] rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="jx-table">
                <thead>
                    <tr>
                        <th>Store name</th>
                        <th>Product name</th>
                        <th>Status</th>
                        <th class="r">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stores as $s)
                        <tr id="view-{{ $s->id }}">
                            <td class="font-medium">{{ $s->name }}</td>
                            <td>{{ $s->product_name }}</td>
                            <td>
                                @if ($s->is_active)
                                    <span class="jx-badge" style="background:var(--lime);border-color:var(--lime);">Active</span>
                                @else
                                    <span class="jx-badge text-[var(--muted)]">Disabled</span>
                                @endif
                            </td>
                            <td class="r whitespace-nowrap">
                                <button type="button" onclick="toggleEdit({{ $s->id }})" class="text-sm font-medium mr-4">Edit</button>
                                <form action="{{ route('ad-spend.sheets.toggle', $s) }}" method="POST" class="inline">
                                    @csrf @method('PUT')
                                    <button type="submit" class="text-sm font-medium {{ $s->is_active ? 'text-red-600' : '' }}">
                                        {{ $s->is_active ? 'Disable' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-{{ $s->id }}" style="display:none;">
                            <td colspan="4" style="background:var(--surface);">
                                <form action="{{ route('ad-spend.sheets.update', $s) }}" method="POST" class="flex flex-wrap gap-2 items-center">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $s->name }}" required class="jx-input flex-1" style="min-width:160px;">
                                    <input type="text" name="product_name" value="{{ $s->product_name }}" required class="jx-input flex-1" style="min-width:160px;">
                                    <button type="submit" class="jx-btn jx-btn-dark" style="height:44px;">Save</button>
                                    <button type="button" onclick="toggleEdit({{ $s->id }})" class="jx-btn jx-btn-round" style="width:auto;padding:0 18px;height:44px;">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-[var(--muted)]" style="padding:32px;">No sheets yet. Add your first one above.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-[var(--muted)] mt-3">Renaming a sheet updates it everywhere. Disabling hides it from the sheet list but keeps all past data.</p>

    <script>
        function toggleEdit(id) {
            var v = document.getElementById('view-' + id), e = document.getElementById('edit-' + id);
            var editing = e.style.display !== 'none';
            e.style.display = editing ? 'none' : '';
            v.style.display = editing ? '' : 'none';
        }
    </script>
@endsection