<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'upgrade_request_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'stripe_session_id',
        'amount',
        'currency',
        'status',
        'description',
        'invoice_url',
        'invoice_pdf',
        'paid_at',
        'due_date',
        'voided_at',
        'payment_method_type',
        'payment_method_last4',
        'payment_method_brand',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'paid_at'   => 'datetime',
        'due_date'  => 'datetime',
        'voided_at' => 'datetime',
    ];

    // ========================================================
    // العلاقات
    // ========================================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CompanySubscription::class, 'subscription_id');
    }

    public function upgradeRequest(): BelongsTo
    {
        return $this->belongsTo(SubscriptionUpgradeRequest::class, 'upgrade_request_id');
    }

    // ========================================================
    // Scopes
    // ========================================================

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    // ========================================================
    // State Checks
    // ========================================================

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isVoided(): bool
    {
        return $this->status === 'void';
    }

    // ========================================================
    // Accessors
    // ========================================================

    /**
     * المبلغ مُنسَّق.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * وصف طريقة الدفع.
     * مثال: "Visa •••• 4242"
     */
    public function getPaymentMethodSummaryAttribute(): ?string
    {
        if (! $this->payment_method_brand || ! $this->payment_method_last4) {
            return null;
        }

        $brand = ucfirst($this->payment_method_brand);
        return "{$brand} •••• {$this->payment_method_last4}";
    }

    // ========================================================
    // Static Helpers
    // ========================================================

    /**
     * إنشاء فاتورة من Stripe Invoice object.
     * يُستدعى من WebhookController.
     */
    public static function createFromStripe(
        Company $company,
        \Stripe\Invoice $stripeInvoice,
        ?int $subscriptionId = null,
        ?int $upgradeRequestId = null
    ): static {
        $paymentIntent = $stripeInvoice->payment_intent;

        return static::create([
            'company_id'               => $company->id,
            'subscription_id'          => $subscriptionId,
            'upgrade_request_id'       => $upgradeRequestId,
            'stripe_invoice_id'        => $stripeInvoice->id,
            'stripe_payment_intent_id' => is_string($paymentIntent)
                                            ? $paymentIntent
                                            : $paymentIntent?->id,
            'amount'                   => $stripeInvoice->amount_paid / 100, // Stripe = cents
            'currency'                 => strtoupper($stripeInvoice->currency),
            'status'                   => $stripeInvoice->status,
            'description'              => $stripeInvoice->description,
            'invoice_url'              => $stripeInvoice->hosted_invoice_url,
            'invoice_pdf'              => $stripeInvoice->invoice_pdf,
            'paid_at'                  => $stripeInvoice->status_transitions->paid_at
                                            ? \Carbon\Carbon::createFromTimestamp(
                                                $stripeInvoice->status_transitions->paid_at
                                              )
                                            : null,
            'due_date'                 => $stripeInvoice->due_date
                                            ? \Carbon\Carbon::createFromTimestamp($stripeInvoice->due_date)
                                            : null,
        ]);
    }

    /**
     * إنشاء فاتورة يدوية (Manual Payment).
     * يُستدعى من ApproveSubscriptionAction عند الموافقة اليدوية.
     */
    public static function createManual(
        Company $company,
        float $amount,
        string $description,
        ?int $subscriptionId = null,
        ?int $upgradeRequestId = null,
        string $currency = 'USD'
    ): static {
        return static::create([
            'company_id'         => $company->id,
            'subscription_id'    => $subscriptionId,
            'upgrade_request_id' => $upgradeRequestId,
            'amount'             => $amount,
            'currency'           => $currency,
            'status'             => 'paid',
            'description'        => $description,
            'paid_at'            => now(),
        ]);
    }
}