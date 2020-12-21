<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForgetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans_choice('mail.forget_password.subject',0).config('app.name'))
            ->greeting(trans_choice('mail.greeting',0))
            ->line(trans_choice('mail.forget_password.line1',0))
            ->line(trans_choice('mail.forget_password.line2',0))
            ->action(trans_choice('mail.forget_password.action',0),
                url('api/v1/reset_password/'.$notifiable->id.'?token='.$notifiable->remember_token))
            ->salutation(trans_choice('mail.salutation',0));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}