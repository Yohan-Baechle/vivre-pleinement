<?php

namespace App\Mail;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCommentNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Comment $comment,
        public string $moderationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Blog] Nouveau commentaire à valider – '.$this->comment->author_name,
            replyTo: [new Address($this->comment->author_email, $this->comment->author_name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-comment-notification',
            with: [
                'authorName' => $this->comment->author_name,
                'postTitle' => $this->comment->post->title,
                'body' => $this->comment->content,
                'moderationUrl' => $this->moderationUrl,
            ],
        );
    }
}
