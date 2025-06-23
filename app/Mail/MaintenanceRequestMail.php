<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MaintenanceRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $productBarcode;
    public string $note;
    public array $files;

    /**
     * Create a new message instance.
     */
    public function __construct(string $productBarcode, string $note, array $files = [])
    {
        $this->productBarcode = $productBarcode;
        $this->note = $note;
        $this->files = $files;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Maintenance Request for ' . $this->productBarcode,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.maintenance.request',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->files as $relativePath) {
            $fullPath = storage_path('app/public/' . $relativePath);

            if (file_exists($fullPath)) {
                $attachments[] = Attachment::fromPath($fullPath)
                    ->as(basename($relativePath)); // give it a clean filename
            }
        }

        return $attachments;
    }
}
