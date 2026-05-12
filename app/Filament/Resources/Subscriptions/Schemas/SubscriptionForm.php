<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('الشركة')
                    ->options(Company::active()->pluck('company_name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('plan_id')
                    ->label('الخطة')
                    ->options(SubscriptionPlan::active()->pluck('name', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $plan = SubscriptionPlan::find($state);
                        if ($plan) {
                            $set('amount_paid', $plan->price);
                        }
                    }),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'    => 'نشط',
                        'trial'     => 'تجربة مجانية',
                        'cancelled' => 'ملغي',
                        'expired'   => 'منتهي',
                        'pending'   => 'معلّق',
                    ])
                    ->default('active')
                    ->required(),

                DateTimePicker::make('starts_at')
                    ->label('تاريخ البداية')
                    ->default(now())
                    ->required(),

                DateTimePicker::make('ends_at')
                    ->label('تاريخ الانتهاء')
                    ->placeholder('اتركه فارغاً للاشتراك غير المحدود'),

                DateTimePicker::make('trial_ends_at')
                    ->label('انتهاء فترة التجربة')
                    ->placeholder('فقط للخطط التجريبية'),

                TextInput::make('amount_paid')
                    ->label('المبلغ المدفوع')
                    ->numeric()
                    ->prefix('$')
                    ->default(0),

                Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'manual'  => 'يدوي (Admin)',
                        'stripe'  => 'Stripe',
                        'paypal'  => 'PayPal',
                        'bank'    => 'تحويل بنكي',
                    ])
                    ->default('manual'),

                TextInput::make('payment_reference')
                    ->label('رقم المرجع / Transaction ID')
                    ->placeholder('اختياري'),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->placeholder('ملاحظات داخلية للأدمن')
                    ->columnSpanFull(),
            ]);
    }
}