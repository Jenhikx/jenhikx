<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('month'))
            ? $request->query('month')
            : now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth();

        $expenses = Expense::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $total = $expenses->sum('amount');

        return view('expenses.index', compact('expenses', 'month', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        Expense::create([
            'date' => $request->date,
            'category' => $request->category,
            'amount' => $request->amount,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('expenses.index', ['month' => substr($request->date, 0, 7)])
            ->with('success', 'Expense added.');
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $expense->update($request->only('date', 'category', 'amount', 'note'));

        return redirect()
            ->route('expenses.index', ['month' => substr($request->date, 0, 7)])
            ->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $month = $expense->date->format('Y-m');
        $expense->delete();

        return redirect()
            ->route('expenses.index', ['month' => $month])
            ->with('success', 'Expense deleted.');
    }
}