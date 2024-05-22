<?php

namespace App\Listeners;

use App\EmailTemplate;
use App\Mail\GeneralMail;
use App\Utilities\Overrider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;

class SendWelcomeNotification {
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        Overrider::load("Settings");
    }

    /**
     * Handle the event.
     *
     * @param  Verified  $event
     * @return void
     */
    public function handle(Verified $event) {
        $user = $event->user;

        if ($user->email_verified_at == null) {
            return;
        }
        //Replace paremeter
        $replace = array(
            '{name}'     => $user->name,
            '{email}'    => $user->email,
            '{valid_to}' => date(get_date_format(), strtotime($user->valid_to)),
        );

        //Send Welcome email
        $template       = EmailTemplate::where('name', 'registration')->first();
        $template->body = process_string($replace, $template->body);

        try {
            Mail::to($user->email)->send(new GeneralMail($template));
        } catch (\Exception $e) {
        //Nothing
        }
    }
}
