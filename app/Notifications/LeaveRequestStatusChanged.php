<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', WhatsAppChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Leave Request Was '.$this->leaveRequest->status->label())
            ->greeting("Hello {$notifiable->name},")
            ->line("Your leave request is now: {$this->leaveRequest->status->label()}.")
            ->when($this->leaveRequest->rejection_reason, fn (MailMessage $mail) => $mail->line("Reason: {$this->leaveRequest->rejection_reason}"))
            ->action('View Request', url('/leave-requests'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'leave_request_status_changed',
            'leave_request_id' => $this->leaveRequest->id,
            'status' => $this->leaveRequest->status->value,
            'message' => "Your leave request is now {$this->leaveRequest->status->label()}.",
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "Your leave request ({$this->leaveRequest->start_date->toDateString()}) is now {$this->leaveRequest->status->label()}.";
    }
}
