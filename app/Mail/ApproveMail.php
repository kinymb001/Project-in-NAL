<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApproveMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $article;
    public $status;
    public $comment;

    public function __construct($article, $status, $comment = null)
    {
        $this->article = $article;
        $this->status = $status;
        $this->comment = $comment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $subject = '';
        $status = ucfirst($this->status);

        if ($status === 'Published') {
            $subject = 'Your article has been published';
        } elseif ($status === 'Reject') {
            $subject = 'Your article has been rejected';
        }

        return $this->view('mail.approve', ['comment' => $this->comment])
            ->subject($subject);
    }
}
