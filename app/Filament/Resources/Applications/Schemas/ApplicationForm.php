<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('job_id')
                    ->label('الوظيفة')
                    ->relationship('job', 'title')
                    ->required(),

                Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'id')
                    ->required(),

                Textarea::make('cover_letter')
                    ->label('خطاب التقديم')
                    ->default(null)
                    ->columnSpanFull(),

                TextInput::make('cv_path')
                    ->label('السيرة الذاتية')
                    ->default(null),

                Textarea::make('about')
                    ->label('نبذة')
                    ->default(null)
                    ->columnSpanFull(),

                TextInput::make('applicant_name')
                    ->label('اسم المتقدم')
                    ->default(null),

                TextInput::make('applicant_email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->default(null),

                TextInput::make('applicant_phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->default(null),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'     => 'قيد الانتظار',
                        'viewed'      => 'تمت المشاهدة',
                        'shortlisted' => 'في القائمة المختصرة',
                        'interview'   => 'مقابلة',
                        'accepted'    => 'مقبول',
                        'rejected'    => 'مرفوض',
                    ])
                    ->default('pending')
                    ->required(),

                Textarea::make('notes')
                    ->label('الملاحظات')
                    ->default(null)
                    ->columnSpanFull(),

                DateTimePicker::make('status_updated_at')
                    ->label('تاريخ تحديث الحالة'),

                DateTimePicker::make('applied_at')
                    ->label('تاريخ التقديم')
                    ->required(),
            ]);
    }
}