<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Statystyki finansowe — DOSTĘP TYLKO Z HASŁEM MENEDŻERA.
 *
 * Dane wrażliwe (zysk, marża, VAT) nie są pokazywane zwykłym pracownikom.
 * Hasło ustawiane w .env: MANAGER_PASSWORD (lub default 'menedzer2026').
 *
 * Session flag: 'manager_auth' = true gdy poprawne hasło wpisane.
 * Sesja wygasa razem z normalną sesją Laravel (zazwyczaj po 2h).
 */
class StatisticsController extends Controller
{
    /**
     * Formularz wpisania hasła menedżera.
     */
    public function loginForm(): View|RedirectResponse
    {
        if (session('manager_auth')) {
            return redirect()->route('statistics.index');
        }
        return view('statistics.login');
    }

    /**
     * Sprawdź hasło menedżera.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $correct = env('MANAGER_PASSWORD', 'menedzer2026');

        if ($request->input('password') === $correct) {
            session(['manager_auth' => true]);
            return redirect()->route('statistics.index')
                ->with('success', 'Zalogowano jako menedżer.');
        }

        return back()->withErrors([
            'password' => 'Nieprawidłowe hasło menedżera.',
        ])->withInput();
    }

    /**
     * Strona główna statystyk.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (!session('manager_auth')) {
            return redirect()->route('statistics.login');
        }

        // Okres do filtrowania (domyślnie ten miesiąc)
        $period = $request->query('period', 'this_month');
        [$startDate, $endDate, $periodLabel] = $this->resolvePeriod($period);

        // Sprzedaże w okresie z relacjami
        $salesInPeriod = Sale::with(['vehicle.purchase', 'vehicle.costs'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        // KPI
        $totalRevenue = $salesInPeriod->sum('sale_price');
        $totalCount = $salesInPeriod->count();

        $totalProfit = $salesInPeriod->sum(fn($sale) => $sale->vehicle->margin() ?? 0);
        $totalCost = $salesInPeriod->sum(fn($sale) => $sale->vehicle->totalCost());
        $avgMargin = $totalCount > 0 ? $totalProfit / $totalCount : 0;
        $avgMarginPercent = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // VAT należny (Margin Scheme: 23/123 od marży)
        $vatDue = $totalProfit > 0 ? ($totalProfit * 23 / 123) : 0;

        // Top 5 najlepszych aut (margin)
        $bestSales = $salesInPeriod
            ->sortByDesc(fn($s) => $s->vehicle->margin() ?? 0)
            ->take(5);

        // Top 5 najgorszych (margin)
        $worstSales = $salesInPeriod
            ->sortBy(fn($s) => $s->vehicle->margin() ?? 0)
            ->take(5);

        // Sprzedaż per miesiąc (12 ostatnich miesięcy)
        $monthlyData = $this->getMonthlyData(12);

        // Wszystkie auta w stocku (aktualnie)
        $stockCount = Vehicle::where('status', 'stock')->count();
        $totalStockValue = Vehicle::where('status', 'stock')
            ->with(['purchase', 'costs'])
            ->get()
            ->sum(fn($v) => $v->totalCost());

        return view('statistics.index', [
            'periodLabel' => $periodLabel,
            'period' => $period,
            'totalRevenue' => $totalRevenue,
            'totalProfit' => $totalProfit,
            'totalCost' => $totalCost,
            'totalCount' => $totalCount,
            'avgMargin' => $avgMargin,
            'avgMarginPercent' => $avgMarginPercent,
            'vatDue' => $vatDue,
            'bestSales' => $bestSales,
            'worstSales' => $worstSales,
            'monthlyData' => $monthlyData,
            'stockCount' => $stockCount,
            'totalStockValue' => $totalStockValue,
        ]);
    }

    /**
     * Wyloguj menedżera (session forget).
     */
    public function logout(): RedirectResponse
    {
        session()->forget('manager_auth');
        return redirect()->route('dashboard')
            ->with('success', 'Wylogowano ze statystyk menedżera.');
    }

    /**
     * Konwersja klucza okresu na daty + label.
     */
    private function resolvePeriod(string $period): array
    {
        $now = now();
        return match ($period) {
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'Ten miesiąc (' . $now->translatedFormat('F Y') . ')',
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
                'Poprzedni miesiąc (' . $now->copy()->subMonth()->translatedFormat('F Y') . ')',
            ],
            'this_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'Ten rok (' . $now->year . ')',
            ],
            'last_year' => [
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
                'Poprzedni rok (' . ($now->year - 1) . ')',
            ],
            'all_time' => [
                now()->subYears(10),
                now()->addDay(),
                'Cały okres',
            ],
            default => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'Ten miesiąc',
            ],
        };
    }

    /**
     * Sprzedaż per miesiąc (do wykresu).
     */
    private function getMonthlyData(int $monthsBack): array
    {
        $data = [];
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $sales = Sale::with('vehicle.purchase', 'vehicle.costs')
                ->whereBetween('sale_date', [$start, $end])
                ->get();

            $data[] = [
                'label' => $month->translatedFormat('M Y'),
                'count' => $sales->count(),
                'revenue' => (float) $sales->sum('sale_price'),
                'profit' => (float) $sales->sum(fn($s) => $s->vehicle->margin() ?? 0),
            ];
        }
        return $data;
    }
}
