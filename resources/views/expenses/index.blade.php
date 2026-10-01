@extends('layouts.app')

@section('title', 'Expenses — JENHIKX')

@section('page-title')
    Track every <span class="jx-chip lime">@include('partials.icon', ['name' => 'expenses'])</span> expense<br>
    in one place
@endsection

@section('content')

    {{-- Month filter --}}
    <form method="GET" action="{{ route('expenses.index') }}" class="mb-5">
        <input type="month" name="month" value="{{ $month }}" class="jx-input" onchange="this.form.submit()">
    </form>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-2xl bg-red-50 text-red-700 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Total --}}
    <div class="jx-card dark mb-5" style="max-width: 320px;">
        <span class="text-sm text-[#B9BEB6]">Total expenses this month</span>
        <p class="text-3xl font-medium tracking-tight mt-3">₹{{ number_format($total, 2) }}</p>
    </div>

    {{-- Add expense --}}
    <div class="jx-card mb-5">
        <p class="text-sm font-semibold mb-4">Add an expense</p>
        <form action="{{ route('expenses.store') }}" method="POST" class="grid gap-3 md:grid-cols-5 items-end">
            @csrf
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Date</label>
                <input type="date" name="date" required value="{{ old('date', now()->toDateString()) }}" class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Category</label>
                <input type="text" name="category" required value="{{ old('category') }}" placeholder="e.g. Office Rent" class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Amount</label>
                <input type="number" step="0.01" min="0.01" name="amount" required value="{{ old('amount') }}" class="jx-input w-full">
            </div>
            <div>
                <label class="block text-xs text-[var(--muted)] mb-1">Note (optional)</label>
                <input type="text" name="note" value="{{ old('note') }}" placeholder="Any extra detail" class="jx-input w-full">
            </div>
            <div>
                <button type="submit" class="jx-btn jx-btn-dark w-full">Add Expense</button>
            </div>
        </form>
    </div>

    {{-- List --}}
    <div class="bg-white border border-[var(--line)] rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="jx-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Note</th>
                        <th class="r">Amount</th>
                        <th class="r">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr id="view-{{ $expense->id }}">
                            <td class="whitespace-nowrap">{{ $expense->date->format('d M Y') }}</td>
                            <td class="font-medium">{{ $expense->category }}</td>
                            <td class="text-[var(--muted)]">{{ $expense->note ?? '-' }}</td>
                            <td class="r">₹{{ number_format($expense->amount, 2) }}</td>
                            <td class="r whitespace-nowrap">
                                <button type="button" onclick="toggleEdit({{ $expense->id }})" class="text-sm font-medium mr-4">Edit</button>
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this expense?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-{{ $expense->id }}" style="display:none;">
                            <td colspan="5" style="background:var(--surface);">
                                <form action="{{ route('expenses.update', $expense) }}" method="POST" class="grid gap-2 md:grid-cols-5 items-end py-2">
                                    @csrf @method('PUT')
                                    <input type="date" name="date" value="{{ $expense->date->format('Y-m-d') }}" required class="jx-input w-full">
                                    <input type="text" name="category" value="{{ $expense->category }}" required class="jx-input w-full">
                                    <input type="text" name="note" value="{{ $expense->note }}" class="jx-input w-full">
                                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ $expense->amount }}" required class="jx-input w-full">
                                    <div class="flex gap-2">
                                        <button type="submit" class="jx-btn jx-btn-dark flex-1" style="height:44px;">Save</button>
                                        <button type="button" onclick="toggleEdit({{ $expense->id }})" class="jx-btn jx-btn-round" style="width:auto;padding:0 18px;height:44px;">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-[var(--muted)]" style="padding:32px;">No expenses logged for this month yet.</td></tr>
                    @endforelse
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