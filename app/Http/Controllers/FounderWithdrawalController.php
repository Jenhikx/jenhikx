<?php

namespace App\Http\Controllers;

use App\Models\FounderWithdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FounderWithdrawalController extends Controller
{
    public const FOUNDERS = ['Kaushik', 'Jenish'];

    public function index(Request $request)
    {
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('month'))
            ? $request->query('month')
            : now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth();

        $withdrawals = FounderWithdrawal::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $total = $withdrawals->sum('amount');

        // Founder-wise breakdown, taaki ek nazar me dikhe kisne kitna nikala
        $byFounder = $withdrawals->groupBy('founder')->map(fn ($rows) => $rows->sum('amount'));

        return view('withdrawals.index', compact('withdrawals', 'month', 'total', 'byFounder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'founder' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        FounderWithdrawal::create([
            'date' => $request->date,
            'founder' => $request->founder,
            'amount' => $request->amount,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('withdrawals.index', ['month' => substr($request->date, 0, 7)])
            ->with('success', 'Withdrawal added.');
    }

    public function update(Request $request, FounderWithdrawal $withdrawal)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'founder' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $withdrawal->update($request->only('date', 'founder', 'amount', 'note'));

        return redirect()
            ->route('withdrawals.index', ['month' => substr($request->date, 0, 7)])
            ->with('success', 'Withdrawal updated.');
    }

    public function destroy(FounderWithdrawal $withdrawal)
    {
        $month = $withdrawal->date->format('Y-m');
        $withdrawal->delete();

        return redirect()
            ->route('withdrawals.index', ['month' => $month])
            ->with('success', 'Withdrawal deleted.');
    }
}