<?php

namespace App\Http\Controllers;

use App\Models\AdditionalIncome;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdditionalIncomeController extends Controller
{
    public function index(Request $request)
    {
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('month'))
            ? $request->query('month')
            : now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth();

        $incomes = AdditionalIncome::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $total = $incomes->sum('amount');

        return view('income.index', compact('incomes', 'month', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'source' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        AdditionalIncome::create([
            'date' => $request->date,
            'source' => $request->source,
            'amount' => $request->amount,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('income.index', ['month' => substr($request->date, 0, 7)])
            ->with('success', 'Income added.');
    }

    public function update(Request $request, AdditionalIncome $income)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'source' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $income->update($request->only('date', 'source', 'amount', 'note'));

        return redirect()
            ->route('income.index', ['month' => substr($request->date, 0, 7)])
            ->with('success', 'Income updated.');
    }

    public function destroy(AdditionalIncome $income)
    {
        $month = $income->date->format('Y-m');
        $income->delete();

        return redirect()
            ->route('income.index', ['month' => $month])
            ->with('success', 'Income deleted.');
    }
}