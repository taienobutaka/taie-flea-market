<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $url;

    /**
     * コンストラクタ
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * メールの構築
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('メールアドレスの確認')
                    ->view('emails.verify-email')
                    ->with(['url' => $this->url]);
    }
}
