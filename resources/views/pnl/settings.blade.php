@extends('layouts.app')

@section('title', 'P&L Settings — JENHIKX')

@section('page-title')
    Set <span class="jx-chip lime">@include('partials.icon', ['name' => 'settings'])</span> rates<br>
    for each sheet
@endsection

@section('header-actions')
    <a href="{{ Route::has('pnl.index') ? route('pnl.index') : '#' }}" class="jx-btn jx-btn-round" style="width:auto;padding:0 20px;">Back to P&amp;L</a>
@endsection

@section('content')

@if ($stores->isEmpty())
    <div class="jx-card text-center py-12">
        <p class="text-lg font-medium">No sheets yet</p>
        <p class="text-sm text-[var(--muted)] mt-1">Add a sheet in Ad Spend first, then set its rates here.</p>
        <a href="{{ route('ad-spend.sheets') }}" class="jx-btn jx-btn-dark mt-5">Go to Manage Sheets</a>
    </div>
@else

    {{-- Sheet selector --}}
    <form method="GET" action="{{ route('pnl.settings') }}" class="mb-4">
        <select name="store" class="jx-input" onchange="this.form.submit()">
            @foreach ($stores as $s)
                <option value="{{ $s->id }}" @selected($s->id === $store->id)>
                    {{ $s->label }}{{ $s->is_active ? '' : ' (disabled)' }}
                </option>
            @endforeach
        </select>
    </form>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-2xl bg-red-50 text-red-700 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Add new rate --}}
    <div class="jx-card mb-4">
        <p class="text-sm font-semibold mb-1">Add a new rate</p>
        <p class="text-xs text-[var(--muted)] mb-4">
            @if ($latest)
                Pre-filled with the current rate. Change only what's different, and set the date it starts from.
            @else
                No rate set yet for this sheet. Fill in all fields and pick the date it starts from.
            @endif
        </p>
        <form action="{{ route('pnl.settings.store') }}" method="POST" class="grid gap-3 md:grid-cols-6">
            @csrf
            <input type="hidden" name="store_id" value="{{ $store->id }}">

            <div class="md:col-span-1">
                <label class="block text-xs text-[var(--muted)] mb-1">From date</label>
                <input type="date" name="effective_from" required class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Delivery %</label>
                <input type="number" step="0.01" min="0" max="100" name="delivery_percent" required
                       value="{{ old('delivery_percent', $latest->delivery_percent ?? '') }}" class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Margin / order</label>
                <input type="number" step="0.01" min="0" name="margin" required
                       value="{{ old('margin', $latest->margin ?? '') }}" class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">RTO charge / order</label>
                <input type="number" step="0.01" min="0" name="rto_charge" required
                       value="{{ old('rto_charge', $latest->rto_charge ?? '') }}" class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Delivered charge / order</label>
                <input type="number" step="0.01" min="0" name="delivered_charge" required
                       value="{{ old('delivered_charge', $latest->delivered_charge ?? '') }}" class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">GST %</label>
                <input type="number" step="0.01" min="0" max="100" name="gst_percent" required
                       value="{{ old('gst_percent', $latest->gst_percent ?? '') }}" class="jx-input w-full">
            </div>

            <div class="md:col-span-6">
                <button type="submit" class="jx-btn jx-btn-dark">Add rate</button>
            </div>
        </form>
    </div>

    {{-- Timeline --}}
    <div class="bg-white border border-[var(--line)] rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="jx-table">
                <thead>
                    <tr>
                        <th>Effective from</th>
                        <th class="r">Delivery %</th>
                        <th class="r">Margin</th>
                        <th class="r">RTO charge</th>
                        <th class="r">Delivered charge</th>
                        <th class="r">GST %</th>
                        <th class="r">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rates as $rate)
                        <tr id="view-{{ $rate->id }}">
                            <td class="font-medium whitespace-nowrap">
                                {{ $rate->effective_from->format('d M Y') }}
                                @if ($loop->last)
                                    <span class="jx-badge ml-2" style="background:var(--lime);border-color:var(--lime);">Current</span>
                                @endif
                            </td>
                            <td class="r">{{ rtrim(rtrim(number_format($rate->delivery_percent, 2), '0'), '.') }}%</td>
                            <td class="r">₹{{ number_format($rate->margin, 2) }}</td>
                            <td class="r">₹{{ number_format($rate->rto_charge, 2) }}</td>
                            <td class="r">₹{{ number_format($rate->delivered_charge, 2) }}</td>
                            <td class="r">{{ rtrim(rtrim(number_format($rate->gst_percent, 2), '0'), '.') }}%</td>
                            <td class="r whitespace-nowrap">
                                <button type="button" onclick="toggleEdit({{ $rate->id }})" class="text-sm font-medium mr-4">Edit</button>
                                <form action="{{ route('pnl.settings.destroy', $rate) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this rate? Days using it will fall back to the next earlier rate.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-{{ $rate->id }}" style="display:none;">
                            <td colspan="7" style="background:var(--surface);">
                                <form action="{{ route('pnl.settings.update', $rate) }}" method="POST" class="grid gap-2 md:grid-cols-6 items-end py-2">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="block text-xs text-[var(--muted)] mb-1">From date</label>
                                        <input type="date" name="effective_from" value="{{ $rate->effective_from->format('Y-m-d') }}" required class="jx-input w-full">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-[var(--muted)] mb-1">Delivery %</label>
                                        <input type="number" step="0.01" name="delivery_percent" value="{{ $rate->delivery_percent }}" required class="jx-input w-full">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-[var(--muted)] mb-1">Margin</label>
                                        <input type="number" step="0.01" name="margin" value="{{ $rate->margin }}" required class="jx-input w-full">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-[var(--muted)] mb-1">RTO charge</label>
                                        <input type="number" step="0.01" name="rto_charge" value="{{ $rate->rto_charge }}" required class="jx-input w-full">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-[var(--muted)] mb-1">Delivered charge</label>
                                        <input type="number" step="0.01" name="delivered_charge" value="{{ $rate->delivered_charge }}" required class="jx-input w-full">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-[var(--muted)] mb-1">GST %</label>
                                        <input type="number" step="0.01" name="gst_percent" value="{{ $rate->gst_percent }}" required class="jx-input w-full">
                                    </div>
                                    <div class="md:col-span-6 flex gap-2">
                                        <button type="submit" class="jx-btn jx-btn-dark" style="height:44px;">Save</button>
                                        <button type="button" onclick="toggleEdit({{ $rate->id }})" class="jx-btn jx-btn-round" style="width:auto;padding:0 18px;height:44px;">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-[var(--muted)]" style="padding:32px;">No rates set for this sheet yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-[var(--muted)] mt-3">Each rate applies from its date until the next one starts. Editing or deleting a rate recalculates P&amp;L for the days it covers.</p>

    <script>
        function toggleEdit(id) {
            var v = document.getElementById('view-' + id), e = document.getElementById('edit-' + id);
            var editing = e.style.display !== 'none';
            e.style.display = editing ? 'none' : '';
            v.style.display = editing ? '' : 'none';
        }
    </script>
@endif
@endsection