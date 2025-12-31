<?php

namespace App\Notifications;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitation extends Notification
{
    use Queueable;

    public $team;

    public $role;

    public $token;

    public $isNewUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(Team $team, $role, $token, $isNewUser = false)
    {
        $this->team = $team;
        $this->role = $role;
        $this->token = $token;
        $this->isNewUser = $isNewUser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->isNewUser) {
            // For new users, they need to set up their account first
            $url = url(route('invitation.show', ['token' => $this->token], false));
            $subject = 'You\'ve been invited to join '.$this->team->name.' on '.config('app.name');
        } else {
            // For existing users, direct team invitation
            $url = url(route('teams.acceptInvitation', ['token' => $this->token], false));
            $subject = 'Team Invitation: Join '.$this->team->name;
        }

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.team-invitation', [
                'teamName' => $this->team->name,
                'role' => $this->role,
                'url' => $url,
                'isNewUser' => $this->isNewUser,
                'userName' => $notifiable->name ?? '',
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
