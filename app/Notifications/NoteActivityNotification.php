<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NoteActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $activity,
        private readonly int $actorId,
        private readonly string $actorName,
        private readonly int $noteId,
        private readonly string $noteTitle,
        private readonly ?string $commentContent = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'activity' => $this->activity,
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'note_id' => $this->noteId,
            'note_title' => $this->noteTitle,
            'comment_content' => $this->commentContent,
        ];
    }
}
