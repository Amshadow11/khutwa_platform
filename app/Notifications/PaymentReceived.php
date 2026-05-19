<?php

namespace App\Notifications;

use App\Models\PaymentInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * إشعار للشركة بعد نجاح الدفع عبر Stripe.
 *
 * يُرسَل لـ: الشركة
 * القناة: database
 *
 * يختلف عن SubscriptionApproved في أنه يحتوي تفاصيل الدفع:
 *   - المبلغ المدفوع
 *   - طريقة الدفع
 *   - رابط الفاتورة
 */
class PaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PaymentInvoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'payment_received',
            'message'         => "✅ تم استلام دفعتك بمبلغ {$this->invoice->formatted_amount}",
            'invoice_id'      => $this->invoice->id,
            'amount'          => $this->invoice->amount,
            'currency'        => $this->invoice->currency,
            'description'     => $this->invoice->description,
            'invoice_url'     => $this->invoice->invoice_url,
            'invoice_pdf'     => $this->invoice->invoice_pdf,
            'paid_at'         => $this->invoice->paid_at?->format('d/m/Y H:i'),
            'payment_method'  => $this->invoice->payment_method_summary,
            'url'             => route('company.subscription.index'),
        ];
    }
}