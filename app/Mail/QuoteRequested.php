<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova solicitação de orçamento — ' . $this->quote->numero,
            replyTo: [$this->quote->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.quote-requested',
            with: ['quote' => $this->quote],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
