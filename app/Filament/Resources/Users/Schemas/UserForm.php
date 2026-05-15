<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label('اسم المستخدم')
                    ->required(),

                TextInput::make('full_name')
                    ->label('الاسم الكامل')
                    ->default(null),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required(),

                DateTimePicker::make('email_verified_at')
                    ->label('تاريخ توثيق البريد'),

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

                TextInput::make('profile_picture')
                    ->label('الصورة الشخصية')
                    ->default(null),

                FileUpload::make('profile_image')
                    ->label('رفع صورة')
                    ->image(),

                Textarea::make('bio')
                    ->label('نبذة')
                    ->default(null)
                    ->columnSpanFull(),

                TextInput::make('address')
                    ->label('العنوان')
                    ->default(null),

                DatePicker::make('birth_date')
                    ->label('تاريخ الميلاد'),

                Select::make('gender')
                    ->label('الجنس')
                    ->options([
                        'male'   => 'ذكر',
                        'female' => 'أنثى',
                    ])
                    ->default(null),

                TextInput::make('linkedin_url')
                    ->label('رابط لينكدإن')
                    ->url()
                    ->default(null),

                TextInput::make('github_url')
                    ->label('رابط GitHub')
                    ->url()
                    ->default(null),

                TextInput::make('portfolio_url')
                    ->label('رابط معرض الأعمال')
                    ->url()
                    ->default(null),

                Textarea::make('skills')
                    ->label('المهارات')
                    ->default(null)
                    ->columnSpanFull(),

                Textarea::make('experience')
                    ->label('الخبرات')
                    ->default(null)
                    ->columnSpanFull(),

                Textarea::make('education')
                    ->label('التعليم')
                    ->default(null)
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'   => 'نشط',
                        'inactive' => 'غير نشط',
                    ])
                    ->default('active')
                    ->required(),

                Toggle::make('is_active')
                    ->label('الحساب مفعل')
                    ->required(),

                Toggle::make('email_verified')
                    ->label('البريد موثّق')
                    ->required(),

                Toggle::make('phone_verified')
                    ->label('الهاتف موثّق')
                    ->required(),

                TextInput::make('role')
                    ->label('الدور')
                    ->required()
                    ->default('job_seeker'),

                DateTimePicker::make('last_login')
                    ->label('آخر تسجيل دخول'),
            ]);
    }
}