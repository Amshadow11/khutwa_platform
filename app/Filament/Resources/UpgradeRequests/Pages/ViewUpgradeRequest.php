<?php

namespace App\Filament\Resources\UpgradeRequests\Pages;

use App\Actions\Subscription\ApproveSubscriptionAction;
use App\Actions\Subscription\RejectSubscriptionAction;
use App\Filament\Resources\UpgradeRequests\UpgradeRequestResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewUpgradeRequest extends ViewRecord
{
    protected static string $resource = UpgradeRequestResource::class;

    /**
     * أزرار الموافقة والرفض في صفحة العرض أيضاً.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('موافقة')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->isPending())
                ->requiresConfirmation()
                ->modalHeading('الموافقة على طلب الترقية')
                ->form([
                    Textarea::make('admin_notes')
                        ->label('ملاحظات داخلية (اختياري)')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    app(ApproveSubscriptionAction::class)->execute(
                        $this->record,
                        auth()->user(),
                        $data['admin_notes'] ?? null
                    );
                    $this->refreshFormData(['status', 'approved_at', 'approved_by']);
                })
                ->successNotificationTitle('تمت الموافقة وتفعيل الاشتراك'),

            Action::make('reject')
                ->label('رفض')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->record->isPending())
                ->requiresConfirmation()
                ->modalHeading('رفض طلب الترقية')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('سبب الرفض')
                        ->required()
                        ->rows(3),

                    Textarea::make('admin_notes')
                        ->label('ملاحظات داخلية (اختياري)')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    app(RejectSubscriptionAction::class)->execute(
                        $this->record,
                        auth()->user(),
                        $data['rejection_reason'],
                        $data['admin_notes'] ?? null
                    );
                    $this->refreshFormData(['status', 'rejected_at', 'rejected_by']);
                })
                ->successNotificationTitle('تم رفض الطلب وإشعار الشركة'),
        ];
    }
}