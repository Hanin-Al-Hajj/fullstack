<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderStatusService;
use App\Support\Whatsapp;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use InvalidArgumentException;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $order = $this->getRecord();

        $nextOptions = collect(OrderStatusService::next($order->status))
            ->mapWithKeys(fn (string $s) => [$s => OrderStatusService::label($s)])
            ->all();

        return [
            // Opens WhatsApp with the customer's number and a ready message; staff only press send.
            Action::make('whatsapp')
                ->label('WhatsApp the customer')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(fn () => Whatsapp::confirmationUrl($this->getRecord()))
                ->openUrlInNewTab(),

            Action::make('changeStatus')
                ->label('Change status')
                ->icon('heroicon-o-arrow-path')
                ->visible($nextOptions !== [])
                ->schema([
                    Select::make('status')->label('New status')->options($nextOptions)->required(),
                    Textarea::make('note')->label('Note (optional, kept in the history)')->rows(2),
                ])
                ->action(function (array $data) {
                    try {
                        app(OrderStatusService::class)->change(
                            $this->getRecord(),
                            $data['status'],
                            $data['note'] ?? null,
                            Filament::auth()->id(),
                        );
                    } catch (InvalidArgumentException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title('Status updated')->success()->send();
                    $this->redirect(OrderResource::getUrl('view', ['record' => $this->getRecord()]));
                }),
        ];
    }
}