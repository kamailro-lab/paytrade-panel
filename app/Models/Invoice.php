<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'sale_id', 'issue_date', 'vat_scheme',
        'vat_amount', 'total_gross', 'pdf_path',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'vat_amount' => 'decimal:2',
        'total_gross' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
