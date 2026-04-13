<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaign extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $campaignSubject,
        public string $campaignBody,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaignSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-campaign',
            with: [
                'campaignSubject' => $this->campaignSubject,
                'campaignBody'    => $this->campaignBody,
            ],
        );
    }
}
