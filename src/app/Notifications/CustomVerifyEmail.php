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
            ->line('初回ログインはこちらから:')
            ->action('プロフィール設定画面へ', url('/create-profile'))
            ->line('もしこのメールに心当たりがない場合は、このメールを無視してください。');
    }
}
