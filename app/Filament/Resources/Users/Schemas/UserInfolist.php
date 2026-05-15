<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('username')
                    ->label('اسم المستخدم'),

                TextEntry::make('full_name')
                    ->label('الاسم الكامل')
                    ->placeholder('-'),

                TextEntry::make('email')
                    ->label('البريد الإلكتروني'),

                TextEntry::make('email_verified_at')
                    ->label('تاريخ توثيق البريد')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('phone')
                    ->label('رقم الهاتف')
                    ->placeholder('-'),

                TextEntry::make('phone_code')
                    ->label('رمز الدولة')
                    ->placeholder('-'),

                TextEntry::make('profile_picture')
                    ->label('الصورة الشخصية')
                    ->placeholder('-'),

                ImageEntry::make('profile_image')
                    ->label('صورة الملف الشخصي')
                    ->placeholder('-'),

                TextEntry::make('bio')
                    ->label('نبذة')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('address')
                    ->label('العنوان')
                    ->placeholder('-'),

                TextEntry::make('birth_date')
                    ->label('تاريخ الميلاد')
                    ->date()
                    ->placeholder('-'),

                TextEntry::make('gender')
                    ->label('الجنس')
                    ->badge()
                    ->placeholder('-'),

                TextEntry::make('linkedin_url')
                    ->label('رابط لينكدإن')
                    ->placeholder('-'),

                TextEntry::make('github_url')
                    ->label('رابط GitHub')
                    ->placeholder('-'),

                TextEntry::make('portfolio_url')
                    ->label('رابط معرض الأعمال')
                    ->placeholder('-'),

                TextEntry::make('skills')
                    ->label('المهارات')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('experience')
                    ->label('الخبرات')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('education')
                    ->label('التعليم')
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('status')
                    ->label('الحالة')
                    ->badge(),

                IconEntry::make('is_active')
                    ->label('الحساب مفعل')
                    ->boolean(),

                IconEntry::make('email_verified')
                    ->label('البريد موثّق')
                    ->boolean(),

                IconEntry::make('phone_verified')
                    ->label('الهاتف موثّق')
                    ->boolean(),

                TextEntry::make('role')
                    ->label('الدور'),

                TextEntry::make('last_login')
                    ->label('آخر تسجيل دخول')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('deleted_at')
                    ->label('تاريخ الحذف')
                    ->dateTime()
                    ->visible(fn (User $record): bool => $record->trashed()),
            ]);
    }
}