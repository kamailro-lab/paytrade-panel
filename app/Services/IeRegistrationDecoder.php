<?php

namespace App\Services;

/**
 * Dekoder irlandzkiego numeru rejestracyjnego.
 *
 * Format post-2013: 131-D-1108
 *   131  → 13 = rok 2013, 1 = I połowa (lipiec-grudzień: 2 = II połowa, np. 132)
 *   D    → kod hrabstwa (Dublin)
 *   1108 → numer kolejny
 *
 * Format 1987-2012: 04-D-12345 (2 cyfry roku, bez prefiksu połowy)
 */
class IeRegistrationDecoder
{
    private const COUNTIES = [
        'C' => 'Cork',
        'CE' => 'Clare',
        'CN' => 'Cavan',
        'CW' => 'Carlow',
        'D' => 'Dublin',
        'DL' => 'Donegal',
        'G' => 'Galway',
        'KE' => 'Kildare',
        'KK' => 'Kilkenny',
        'KY' => 'Kerry',
        'L' => 'Limerick',
        'LD' => 'Longford',
        'LH' => 'Louth',
        'LK' => 'Limerick',
        'LM' => 'Leitrim',
        'LS' => 'Laois',
        'MH' => 'Meath',
        'MN' => 'Monaghan',
        'MO' => 'Mayo',
        'OY' => 'Offaly',
        'RN' => 'Roscommon',
        'SO' => 'Sligo',
        'T' => 'Tipperary',
        'TN' => 'Tipperary North',
        'TS' => 'Tipperary South',
        'W' => 'Waterford',
        'WD' => 'Waterford',
        'WH' => 'Westmeath',
        'WW' => 'Wicklow',
        'WX' => 'Wexford',
    ];

    public function decode(string $registration): ?array
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $registration));

        // Format 3-cyfrowy (2013+): 131-D-1108
        if (preg_match('/^(\d{2})([12])([A-Z]{1,2})(\d{1,6})$/', $clean, $m)) {
            $year = 2000 + (int) $m[1];
            $half = (int) $m[2] === 1 ? 'I połowa' : 'II połowa';
            $countyCode = $m[3];
            $county = self::COUNTIES[$countyCode] ?? $countyCode;

            return [
                'year' => $year,
                'half' => $half,
                'county_code' => $countyCode,
                'county' => $county,
                'sequence' => (int) $m[4],
                'normalized' => "{$m[1]}{$m[2]}-{$countyCode}-{$m[4]}",
                'display' => "📅 {$year} ({$half}) · 📍 {$county}",
            ];
        }

        // Format 2-cyfrowy (1987-2012): 04-D-12345
        if (preg_match('/^(\d{2})([A-Z]{1,2})(\d{1,6})$/', $clean, $m)) {
            $yy = (int) $m[1];
            // 87-99 = 1987-1999, 00-12 = 2000-2012
            $year = $yy >= 87 ? 1900 + $yy : 2000 + $yy;
            $countyCode = $m[2];
            $county = self::COUNTIES[$countyCode] ?? $countyCode;

            return [
                'year' => $year,
                'half' => null,
                'county_code' => $countyCode,
                'county' => $county,
                'sequence' => (int) $m[3],
                'normalized' => "{$m[1]}-{$countyCode}-{$m[3]}",
                'display' => "📅 {$year} · 📍 {$county}",
            ];
        }

        return null;
    }
}
