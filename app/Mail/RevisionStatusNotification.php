<?php

namespace App\Mail;

use App\Models\RevisionArticle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RevisionStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $revision;
    public $status;
    public $reason;

    public function __construct(RevisionArticle $revision, $status, $reason)
    {
        $this->revision = $revision;
        $this->status = $status;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->view('mail.revision_status_notification')
            ->subject('Revision Status Notification');
    }
}
