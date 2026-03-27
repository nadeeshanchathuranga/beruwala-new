<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\TillTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TillManagementController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $activeShift = Shift::query()
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (!$activeShift) {
            return redirect()
                ->route('shifts.create')
                ->with('error', 'Start a shift before recording till transactions.');
        }

        $totals = $this->calculateTotals($activeShift);

        $transactions = TillTransaction::query()
            ->forShift($activeShift->id)
            ->with('user:id,name')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Till/Index', [
            'activeShift' => $activeShift,
            'transactions' => $transactions,
            'totals' => $totals,
            'availableCashRule' => config('pos.available_cash_rule'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $activeShift = Shift::query()
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (!$activeShift) {
            return redirect()
                ->route('shifts.create')
                ->with('error', 'Start a shift before recording till transactions.');
        }

        $validated = $request->validate([
            'transaction_type' => ['required', 'in:cash_in,cash_out'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $amount = round((float) $validated['amount'], 2);
        $totals = $this->calculateTotals($activeShift);

        if ($validated['transaction_type'] === 'cash_out' && $amount > (float) $totals['available_cash']) {
            return back()->withErrors([
                'amount' => 'Cash out exceeds available cash for current rule.',
            ]);
        }

        TillTransaction::create([
            'shift_id' => $activeShift->id,
            'user_id' => Auth::id(),
            'transaction_type' => $validated['transaction_type'],
            'amount' => $amount,
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()
            ->route('till.index')
            ->with('success', 'Till transaction recorded successfully.');
    }

    private function calculateTotals(Shift $shift): array
    {
        $cashIn = (float) TillTransaction::query()
            ->forShift($shift->id)
            ->where('transaction_type', 'cash_in')
            ->sum('amount');

        $cashOut = (float) TillTransaction::query()
            ->forShift($shift->id)
            ->where('transaction_type', 'cash_out')
            ->sum('amount');

        $salesTotal = (float) $shift->sales()->sum('net_amount');

        $rule = (string) config('pos.available_cash_rule', 'opening_plus_sales_plus_cash_in_minus_cash_out');

        if ($rule === 'opening_plus_cash_in_minus_cash_out') {
            $availableCash = (float) $shift->start_amount + $cashIn - $cashOut;
        } else {
            $availableCash = (float) $shift->start_amount + $salesTotal + $cashIn - $cashOut;
        }

        return [
            'opening_amount' => round((float) $shift->start_amount, 2),
            'sales_total' => round($salesTotal, 2),
            'cash_in_total' => round($cashIn, 2),
            'cash_out_total' => round($cashOut, 2),
            'net_till_movement' => round($cashIn - $cashOut, 2),
            'balance' => round((float) $shift->start_amount + $cashIn - $cashOut, 2),
            'available_cash' => round(max(0, $availableCash), 2),
        ];
    }
}
