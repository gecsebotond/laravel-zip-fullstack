<?php

namespace App\Mail;

use App\Models\County;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CountyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $county;
    public $pdfContent;

    public function __construct(County $county, $pdfContent)
    {
        $this->county = $county;
        $this->pdfContent = $pdfContent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->county->name . ' Megye Jelentés',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.county_report',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'county_' . $this->county->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}