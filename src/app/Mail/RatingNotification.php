<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RatingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $purchaser;
    public $seller;
    public $item;
    public $rating;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($purchaser, $seller, $item, $rating)
    {
        $this->purchaser = $purchaser;
        $this->seller = $seller;
        $this->item = $item;
        $this->rating = $rating;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('商品評価のお知らせ')
                    ->view('emails.rating-notification');
    }
}
