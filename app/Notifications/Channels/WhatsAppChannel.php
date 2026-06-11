<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends notifications over WhatsApp via a configurable HTTP gateway.
 *
 * Configure `services.whatsapp` (endpoint + token). When no endpoint is set
 * (local/dev/test), messages are logged instead of dispatched.
 */
class WhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $to = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (! $to) {
            return;
        }

        /** @var string $message */
        $message = $notification->toWhatsApp($notifiable);

        $endpoint = config('services.whatsapp.endpoint');

        if (! $endpoint) {
            Log::info('[WhatsApp:stub] message not sent (no gateway configured)', [
                'to' => $to,
                'message' => $message,
            ]);

            return;
        }

        Http::withToken((string) config('services.whatsapp.token'))
            ->asJson()
            ->post($endpoint, [
                'to' => $to,
                'message' => $message,
            ]);
    }
}
