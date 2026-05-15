<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label('اسم الشركة')
                    ->required(),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required(),

                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->required(),

                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->default(null),

                TextInput::make('phone_code')
                    ->label('رمز الدولة')
                    ->tel()
                    ->default('YE'),

                TextInput::make('logo')
                    ->label('شعار الشركة')
                    ->default(null),

                TextInput::make('profile_picture')
                    ->label('الصورة الشخصية')
                    ->default(null),

                Textarea::make('description')
                    ->label('وصف الشركة')
                    ->default(null)
                    ->columnSpanFull(),

                TextInput::make('address')
                    ->label('العنوان')
                    ->default(null),

                TextInput::make('website')
                    ->label('الموقع الإلكتروني')
                    ->url()
                    ->default(null),

                TextInput::make('industry')
                    ->label('القطاع')
                    ->default(null),

                TextInput::make('founded_year')
                    ->label('سنة التأسيس')
                    ->default(null),

                Select::make('company_size')
                    ->label('حجم الشركة')
                    ->options([
                        'startup' => 'ناشئة',
                        'small'   => 'صغيرة',
                        'medium'  => 'متوسطة',
                        'large'   => 'كبيرة',
                    ])
                    ->default('small')
                    ->required(),

                TextInput::make('subscription_plan')
                    ->label('خطة الاشتراك')
                    ->required()
                    ->default('free'),

                Toggle::make('subscription')
                    ->label('يوجد اشتراك')
                    ->required(),

                DatePicker::make('subscription_started')
                    ->label('تاريخ بداية الاشتراك'),

                DatePicker::make('subscription_end')
                    ->label('تاريخ انتهاء الاشتراك'),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'   => 'نشط',
                        'inactive' => 'غير نشط',
                        'pending'  => 'قيد الانتظار',
                    ])
                    ->default('pending')
                    ->required(),

                Toggle::make('is_verified')
                    ->label('الحساب موثّق')
                    ->required(),

                TextInput::make('views')
                    ->label('عدد المشاهدات')
                    ->required()
                    ->numeric()
                    ->default(0),

                TextInput::make('role')
                    ->label('الدور')
                    ->required()
                    ->default('company'),

                DateTimePicker::make('last_login')
                    ->label('آخر تسجيل دخول'),
            ]);
    }
}