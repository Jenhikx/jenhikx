<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('month'))
            ? $request->query('month')
            : now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth();

        // Pinned notes hamesha upar, month kuch bhi ho
        $notes = Note::with('creator')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('is_pinned')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('notes.index', compact('notes', 'month'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'content' => ['required', 'string'],
        ]);

        Note::create([
            'title' => $request->title,
            'date' => $request->date,
            'content' => $request->content,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('notes.index', ['month' => substr($request->date, 0, 7)])
            ->with('success', 'Note added.');
    }

    public function update(Request $request, Note $note)
    {
        $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'content' => ['required', 'string'],
        ]);

        $note->update($request->only('title', 'date', 'content'));

        return redirect()
            ->route('notes.index', ['month' => substr($request->date, 0, 7)])
            ->with('success', 'Note updated.');
    }

    public function togglePin(Note $note)
    {
        $note->update(['is_pinned' => !$note->is_pinned]);

        return redirect()
            ->route('notes.index', ['month' => $note->date->format('Y-m')])
            ->with('success', $note->is_pinned ? 'Note pinned.' : 'Note unpinned.');
    }

    public function destroy(Note $note)
    {
        $month = $note->date->format('Y-m');
        $note->delete();

        return redirect()->route('notes.index', ['month' => $month])->with('success', 'Note deleted.');
    }
}