<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Shift;
use App\Models\TillTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $shifts = Shift::with(['user:id,name'])
            ->withCount('transactions')
            ->withSum('transactions as transactions_total', 'amount')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    })->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Shift/Index', [
            'shifts' => $shifts,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Shift/Create', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'start_time' => ['required', 'date'],
            'start_amount' => ['required', 'numeric', 'min:0'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'end_amount' => ['nullable', 'numeric', 'min:0'],
            'total_sales' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:open,closed'],
        ]);

        $shift = Shift::create($validated);

        return redirect()
            ->route('shifts.show', $shift)
            ->with('success', 'Shift created successfully.');
    }

    public function show(Shift $shift)
    {
        $shift->load([
            'user:id,name',
            'transactions' => function ($query) {
                $query->with('user:id,name')->latest();
            },
        ]);

        $cashIn = (float) TillTransaction::where('shift_id', $shift->id)
            ->where('transaction_type', 'cash_in')
            ->sum('amount');

        $cashOut = (float) TillTransaction::where('shift_id', $shift->id)
            ->where('transaction_type', 'cash_out')
            ->sum('amount');

        return Inertia::render('Shift/Show', [
            'shift' => $shift,
            'summary' => [
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'net' => $cashIn - $cashOut,
            ],
        ]);
    }

    public function edit(Shift $shift)
    {
        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Shift/Edit', [
            'shift' => $shift,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'start_time' => ['required', 'date'],
            'start_amount' => ['required', 'numeric', 'min:0'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'end_amount' => ['nullable', 'numeric', 'min:0'],
            'total_sales' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:open,closed'],
        ]);

        $isClosingShift = $shift->status === 'open' && $validated['status'] === 'closed';

        if ($isClosingShift) {
            $validated = $this->processShiftClosing($shift, $validated);
        }

        $shift->update($validated);

        return redirect()
            ->route('shifts.show', $shift)
            ->with('success', $isClosingShift
                ? 'Shift closed successfully. Sales total has been auto-calculated.'
                : 'Shift updated successfully.');
    }

    private function processShiftClosing(Shift $shift, array $validated): array
    {
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'] ?? now();

        $salesTotal = (float) Sale::where('user_id', $validated['user_id'])
            ->whereBetween('created_at', [$startTime, $endTime])
            ->sum('net_amount');

        $cashIn = (float) TillTransaction::where('shift_id', $shift->id)
            ->where('transaction_type', 'cash_in')
            ->sum('amount');

        $cashOut = (float) TillTransaction::where('shift_id', $shift->id)
            ->where('transaction_type', 'cash_out')
            ->sum('amount');

        $validated['total_sales'] = round($salesTotal, 2);
        $validated['end_time'] = $endTime;

        // If end amount is not provided, auto-calculate expected closing cash.
        if (!isset($validated['end_amount']) || $validated['end_amount'] === null || $validated['end_amount'] === '') {
            $validated['end_amount'] = round(
                (float) $validated['start_amount'] + $salesTotal + $cashIn - $cashOut,
                2
            );
        }

        return $validated;
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()
            ->route('shifts.index')
            ->with('success', 'Shift deleted successfully.');
    }
}
