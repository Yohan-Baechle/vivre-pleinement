<?php

namespace App\Mail;

use App\Models\BookOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookOrderNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public BookOrder $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Livre] Nouvelle commande – '.$this->order->customerName(),
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing('product');

        return new Content(markdown: 'mail.book-order-notification');
    }
}
