<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Shift;
use App\Models\TillTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function index(Request $request): Response
    {
        $activeShift = Shift::query()
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest('id')
            ->first();

        $shifts = Shift::with(['user:id,name'])
            ->withCount('transactions')
            ->withSum('transactions as transactions_total', 'amount')
            ->when($request->filled('mine'), function ($query) {
                $query->where('user_id', Auth::id());
            })
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
            'filters' => $request->only(['status', 'search', 'mine']),
            'activeShift' => $activeShift,
        ]);
    }

    public function create(): Response|RedirectResponse
    {
        $activeShift = Shift::query()
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if ($activeShift) {
            return redirect()
                ->route('shifts.index')
                ->with('error', 'You already have an open shift. End it before starting a new one.');
        }

        return Inertia::render('Shift/Create', [
            'startedAt' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'start_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $hasOpenShift = Shift::query()
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->exists();

        if ($hasOpenShift) {
            return redirect()
                ->route('shifts.index')
                ->with('error', 'You already have an open shift. End it before starting a new one.');
        }

        $shift = Shift::create([
            'user_id' => Auth::id(),
            'start_time' => now(),
            'start_amount' => $validated['start_amount'],
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('shifts.show', $shift)
            ->with('success', 'Shift started successfully.')
            ->with('shift_event', [
                'type' => 'started',
                'shift_id' => $shift->id,
            ]);
    }

    public function show(Shift $shift): Response
    {
        $shift->load([
            'user:id,name',
            'transactions' => function ($query) {
                $query->with('user:id,name')->latest();
            },
        ]);

        $summary = $this->buildCashSummary($shift);

        $isCurrentUsersOpenShift =
            (int) $shift->user_id === (int) Auth::id() &&
            $shift->status === 'open';

        return Inertia::render('Shift/Show', [
            'shift' => $shift,
            'summary' => $summary,
            'isCurrentUsersOpenShift' => $isCurrentUsersOpenShift,
        ]);
    }

    public function edit(Shift $shift): Response|RedirectResponse
    {
        if ($shift->status !== 'open') {
            return redirect()
                ->route('shifts.show', $shift)
                ->with('error', 'Only open shifts can be closed.');
        }

        if ((int) $shift->user_id !== (int) Auth::id()) {
            return redirect()
                ->route('shifts.show', $shift)
                ->with('error', 'You can only close your own active shift.');
        }

        $summary = $this->buildCashSummary($shift);

        return Inertia::render('Shift/Edit', [
            'shift' => $shift,
            'summary' => $summary,
        ]);
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        if ($shift->status !== 'open') {
            return redirect()
                ->route('shifts.show', $shift)
                ->with('error', 'This shift is already closed.');
        }

        if ((int) $shift->user_id !== (int) Auth::id()) {
            return redirect()
                ->route('shifts.show', $shift)
                ->with('error', 'You can only close your own active shift.');
        }

        $validated = $request->validate([
            'end_amount' => ['required', 'numeric', 'min:0'],
            'closing_notes' => ['nullable', 'string'],
        ]);

        $summary = $this->buildCashSummary($shift);
        $closingCash = (float) $validated['end_amount'];
        $variance = round($closingCash - (float) $summary['expected_cash'], 2);

        $shift->update([
            'end_time' => now(),
            'end_amount' => $closingCash,
            'total_sales' => $summary['sales_total'],
            'expected_cash' => $summary['expected_cash'],
            'variance_amount' => $variance,
            'closing_notes' => $validated['closing_notes'] ?? null,
            'status' => 'closed',
        ]);

        return redirect()
            ->route('shifts.show', $shift)
            ->with('success', 'Shift ended successfully. Cash variance has been calculated.')
            ->with('shift_event', [
                'type' => 'ended',
                'shift_id' => $shift->id,
            ]);
    }

    private function buildCashSummary(Shift $shift): array
    {
        $salesTotal = (float) Sale::query()
            ->where('shift_id', $shift->id)
            ->sum('net_amount');

        $cashIn = (float) TillTransaction::query()
            ->where('shift_id', $shift->id)
            ->where('transaction_type', 'cash_in')
            ->sum('amount');

        $cashOut = (float) TillTransaction::query()
            ->where('shift_id', $shift->id)
            ->where('transaction_type', 'cash_out')
            ->sum('amount');

        $expectedCash = round((float) $shift->start_amount + $salesTotal + $cashIn - $cashOut, 2);

        return [
            'sales_total' => round($salesTotal, 2),
            'cash_in' => round($cashIn, 2),
            'cash_out' => round($cashOut, 2),
            'net' => round($cashIn - $cashOut, 2),
            'expected_cash' => $expectedCash,
            'variance_amount' => $shift->variance_amount !== null
                ? round((float) $shift->variance_amount, 2)
                : null,
        ];
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        if ($shift->status === 'open') {
            return redirect()
                ->route('shifts.index')
                ->with('error', 'Open shifts cannot be deleted. End the shift first.');
        }

        $shift->delete();

        return redirect()
            ->route('shifts.index')
            ->with('success', 'Shift deleted successfully.');
    }
}
