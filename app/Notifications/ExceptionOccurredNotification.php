<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use Throwable;

class ExceptionOccurredNotification extends Notification
{
    protected $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $telegram_id = env('TELEGRAM_CRISP_AIRTABLE');
        $msg = sprintf("%s \n%s Line:%d", $this->exception->getMessage(), $this->exception->getFile(), $this->exception->getLine());

        return TelegramMessage::create()
            ->to($telegram_id)
            ->content($msg);

    }
}