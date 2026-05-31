<?php

namespace App\Services;

use App\Models\Contractor;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Importer CSV eksportowanego z Google Sheets (Stock / Sold).
 *
 * Format Sheets (z screenshotów Paytrade/MRtardex):
 *   STOCK: DATA, MAKE/MODEL, REG., MILEAGE, PRICE PURCHASE, PRICE, Payment, DONE DEAL, WWW, motortrans, ORDER
 *   SOLD: + SELLING PRICE, KOSZTY, DATA SPRZ, ODBIORCA, NR. UMOWY KREDYT,
 *         KREDYT, BANK, GOTÓWKA LUB DEPOSIT, TRADE, GWARANCJA, KONTAKT, E-MAIL, Eir code
 */
class SheetsImporter
{
    private array $stats = [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public function importCsv(string $path): array
    {
        $this->stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        $rows = $this->readCsv($path);
        if (count($rows) < 2) {
            $this->stats['errors'][] = 'Plik pusty lub brak nagłówków.';
            return $this->stats;
        }

        $header = array_map([$this, 'normalizeHeader'], array_shift($rows));
        $isSold = in_array('SELLING_PRICE', $header) || in_array('DATA_SPRZ', $header);

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;

            // Wyrównaj długość row do header
            $row = array_pad(array_slice($row, 0, count($header)), count($header), '');
            $data = array_combine($header, $row);

            // Pomiń puste wiersze (czerwone summary rows itp.)
            if (empty(trim($data['REG'] ?? '')) && empty(trim($data['MAKE_MODEL'] ?? ''))) {
                continue;
            }

            try {
                DB::transaction(function () use ($data, $isSold) {
                    $vehicle = $this->upsertVehicle($data);
                    $this->upsertPurchase($vehicle, $data);
                    if ($isSold) {
                        $this->upsertSale($vehicle, $data);
                    }
                });
            } catch (\Throwable $e) {
                $this->stats['skipped']++;
                $regForError = trim($data['REG'] ?? '');
                $this->stats['errors'][] = "Wiersz {$rowNum} ({$regForError}): " . $e->getMessage();
            }
        }

        return $this->stats;
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }
        return $rows;
    }

    private function normalizeHeader(string $h): string
    {
        $h = trim($h);
        if (class_exists(\Transliterator::class)) {
            $t = \Transliterator::create('Any-Latin; Latin-ASCII');
            if ($t) {
                $h = $t->transliterate($h);
            }
        } elseif (function_exists('iconv')) {
            $h = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $h) ?: $h;
        }
        $h = strtoupper(preg_replace('/[^A-Z0-9]/i', '_', $h));
        return trim(preg_replace('/_+/', '_', $h), '_');
    }

    private function upsertVehicle(array $data): Vehicle
    {
        $reg = $this->normalizeRegistration($data['REG'] ?? '');
        if ($reg === '') {
            throw new \RuntimeException('Brak rejestracji.');
        }

        [$make, $model] = $this->splitMakeModel($data['MAKE_MODEL'] ?? '');
        $mileage = $this->parseMileage($data['MILEAGE'] ?? '');

        $vehicle = Vehicle::firstOrNew(['registration' => $reg]);
        $isNew = !$vehicle->exists;

        // Tylko nadpisuj jeśli nowa wartość ma sens (nie nadpisuj 'Audi' przez 'Unknown')
        if ($make !== 'Unknown' || empty($vehicle->make)) {
            $vehicle->make = $make;
        }
        if ($model !== '' || empty($vehicle->model)) {
            $vehicle->model = $model !== '' ? $model : '—';
        }

        if ($mileage['value']) {
            $vehicle->mileage_km = $mileage['value'];
            $vehicle->mileage_unit = $mileage['unit'];
        }

        $orderSrc = $this->safeString($data['ORDER'] ?? '', 60);
        if ($orderSrc) $vehicle->order_source = $orderSrc;

        $doneDeal = $this->safeString($data['DONE_DEAL'] ?? '', 6);
        if ($doneDeal) $vehicle->done_deal = $doneDeal;

        $www = $this->safeString($data['WWW'] ?? '', 6);
        if ($www) $vehicle->www_listed = $www;

        if (!empty(trim($data['MOTORTRANS'] ?? ''))) {
            $vehicle->motortrans = true;
        }

        if ($isNew) {
            $vehicle->status = 'stock';
        }

        $vehicle->save();

        $isNew ? $this->stats['created']++ : $this->stats['updated']++;

        return $vehicle;
    }

    private function upsertPurchase(Vehicle $vehicle, array $data): void
    {
        $price = $this->parseMoney($data['PRICE_PURCHASE'] ?? '');
        $purchaseDate = $this->parseDate($data['DATA'] ?? '');

        if (!$price && !$purchaseDate) {
            return;
        }

        $supplier = $this->resolveContractor(
            $data['ORDER'] ?? null,
            null, null, null,
            'supplier'
        );

        $purchase = $vehicle->purchase ?: new Purchase(['vehicle_id' => $vehicle->id]);
        $purchase->fill([
            'contractor_id' => $supplier?->id,
            'purchase_date' => $purchaseDate ?: now(),
            'purchase_price' => $price ?? 0,
            'currency' => 'EUR',
            'vrt_paid' => 0,
            'transport_cost' => 0,
        ])->save();
    }

    private function upsertSale(Vehicle $vehicle, array $data): void
    {
        $salePrice = $this->parseMoney($data['SELLING_PRICE'] ?? '');
        $saleDate = $this->parseDate($data['DATA_SPRZ'] ?? '');

        if (!$salePrice) {
            return;
        }

        $customer = $this->resolveContractor(
            $data['ODBIORCA'] ?? null,
            $data['KONTAKT'] ?? null,
            $data['E_MAIL'] ?? null,
            $data['EIR_CODE'] ?? null,
            'customer'
        );

        if (!$customer) {
            $customer = Contractor::firstOrCreate(
                ['name' => 'Klient (brak danych)'],
                ['type' => 'customer']
            );
        }

        $sale = $vehicle->sale ?: new Sale(['vehicle_id' => $vehicle->id]);
        $sale->fill([
            'contractor_id' => $customer->id,
            'sale_date' => $saleDate ?: now(),
            'sale_price' => $salePrice,
            'payment_method' => $this->mapPaymentMethod($data['TYP_PLATNOSCI'] ?? ''),
            'payment_credit' => $this->parseMoney($data['KREDYT'] ?? '') ?? 0,
            'payment_bank' => $this->parseMoney($data['BANK'] ?? '') ?? 0,
            'payment_cash_deposit' => $this->parseMoney($data['GOTOWKA_LUB_DEPOSIT'] ?? '') ?? 0,
            'payment_trade' => $this->parseMoney($data['TRADE'] ?? '') ?? 0,
            'credit_contract_number' => $this->safeString($data['NR_UMOWY_KREDYT'] ?? '', 30),
            'warranty' => $this->safeString($data['GWARANCJA'] ?? '', 40),
        ])->save();

        $vehicle->update(['status' => 'sold']);
    }

    private function resolveContractor(?string $name, ?string $phone, ?string $email, ?string $eirCode, string $type): ?Contractor
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $contractor = Contractor::firstOrNew(['name' => $name]);
        if (!$contractor->exists) {
            $contractor->type = $type;
        } elseif ($contractor->type !== $type && $contractor->type !== 'both') {
            $contractor->type = 'both';
        }

        if ($phone && !$contractor->phone) {
            $contractor->phone = $this->safeString($phone, 30);
        }
        if ($email && !$contractor->email && filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            $contractor->email = trim($email);
        }
        if ($eirCode && !$contractor->eir_code) {
            $contractor->eir_code = strtoupper(preg_replace('/\s+/', '', $eirCode));
        }
        $contractor->save();

        return $contractor;
    }

    private function normalizeRegistration(string $reg): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $reg));
        if (preg_match('/^(\d{2,3})([A-Z]{1,2})(\d{1,6})$/', $clean, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }
        return $clean;
    }

    private function splitMakeModel(string $s): array
    {
        $s = trim($s);
        if ($s === '') {
            return ['Unknown', ''];
        }
        $parts = preg_split('/\s+/', $s, 2);
        return [$parts[0], $parts[1] ?? ''];
    }

    private function parseMileage(string $s): array
    {
        $s = strtolower(trim($s));
        $unit = str_contains($s, 'mil') ? 'mil' : 'km';
        $value = (int) preg_replace('/[^0-9]/', '', $s);
        return ['value' => $value ?: null, 'unit' => $unit];
    }

    private function parseMoney(string $s): ?float
    {
        $clean = trim((string) $s);
        if ($clean === '') return null;
        $clean = preg_replace('/[^\d,.\-]/', '', $clean);
        $clean = str_replace(',', '.', $clean);
        if ($clean === '' || !is_numeric($clean)) return null;
        return (float) $clean;
    }

    private function parseDate(string $s): ?Carbon
    {
        $s = trim($s);
        if ($s === '') return null;

        // DD/MM/YYYY or D/M/YYYY
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{2,4})$#', $s, $m)) {
            $y = (int) $m[3];
            if ($y < 100) $y += 2000;
            try {
                return Carbon::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $y, $m[2], $m[1]));
            } catch (\Throwable) {
                return null;
            }
        }
        try {
            return Carbon::parse($s);
        } catch (\Throwable) {
            return null;
        }
    }

    private function mapPaymentMethod(string $s): string
    {
        $s = strtolower(trim($s));
        return match (true) {
            $s === 'mt' => 'other',
            $s === 'cash' => 'cash',
            str_contains($s, 'bank') => 'bank_transfer',
            str_contains($s, 'card') => 'card',
            str_contains($s, 'fin') => 'financing',
            default => 'bank_transfer',
        };
    }

    private function safeString(?string $s, int $max): ?string
    {
        $s = trim((string) $s);
        return $s === '' ? null : mb_substr($s, 0, $max);
    }
}
