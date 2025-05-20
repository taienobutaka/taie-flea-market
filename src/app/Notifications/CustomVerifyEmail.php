<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail
{
    /**
     * メール通知の内容を定義
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('メールアドレスの確認')
            ->line('以下のリンクをクリックして、メールアドレスの確認を完了してください。')
            ->action('メールアドレスを確認する', $verificationUrl)
            ->line('メール認証完了後は、ログイン画面からログインしてください。')
            ->line('もしこのメールに心当たりがない場合は、このメールを無視してください。');
    }
}
