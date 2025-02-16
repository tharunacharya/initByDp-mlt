<?php
/*
@copyright

Fleet Manager v6.5

Copyright (C) 2017-2023 Hyvikk Solutions <https://hyvikk.com/> All rights reserved.
Design and developed by Hyvikk Solutions <https://hyvikk.com/>

 */
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ServiceReminderNotification extends Notification {
	use Queueable;

	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	//date = new due date
	//id = service_reminder->id
	public $message;
	public $vid;
	public $date;

	public function __construct($message, $vid, $date) {
		$this->message = $message;
		$this->vid = $vid;
		$this->date = $date;
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed  $notifiable
	 * @return array
	 */
	public function via($notifiable) {
		return ['database'];
	}

	/**
	 * Get the mail representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail($notifiable) {
		return (new MailMessage)
			->line('The introduction to the notification.')
			->action('Notification Action', url('/'))
			->line('Thank you for using our application!');
	}

	/**
	 * Get the array representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return array
	 */
	public function toArray($notifiable) {
		return [
			'msg' => $this->message,
			'vid' => $this->vid,
			'date' => $this->date,
		];
	}
}


// SOS

class SOSNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('SOS Alert')
                    ->line('An SOS alert has been triggered.')
                    ->action('Check Now', url('/admin/dashboard'))
                    ->line('Please take immediate action!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'An SOS alert has been triggered. Please take immediate action!',
            'url' => url('/admin/dashboard')
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'An SOS alert has been triggered. Please take immediate action!',
            'url' => url('/admin/dashboard')
        ]);
    }
}

