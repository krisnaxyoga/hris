<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification implements ShouldQueue
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
        $employee = $this->leaveRequest->employee;

        return (new MailMessage)
            ->subject('Leave Request Awaiting Your Approval')
            ->greeting("Hello {$notifiable->name},")
            ->line("{$employee->full_name} has requested leave that needs your approval.")
            ->line("Type: {$this->leaveRequest->leaveType->name}")
            ->line("Dates: {$this->leaveRequest->start_date->toDateString()} → {$this->leaveRequest->end_date->toDateString()} ({$this->leaveRequest->total_days} days)")
            ->action('Review Request', url('/leave-approvals'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'leave_request_submitted',
            'leave_request_id' => $this->leaveRequest->id,
            'employee' => $this->leaveRequest->employee->full_name,
            'status' => $this->leaveRequest->status->value,
            'message' => "{$this->leaveRequest->employee->full_name} requested leave awaiting your approval.",
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $employee = $this->leaveRequest->employee;

        return "Leave approval needed: {$employee->full_name} requested {$this->leaveRequest->total_days} day(s) "
            ."from {$this->leaveRequest->start_date->toDateString()}.";
    }
}
