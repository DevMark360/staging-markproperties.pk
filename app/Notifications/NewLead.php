<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the sales inbox about a new lead (D2), so leads aren't missed between admin checks.
 * Sent after the visitor already has their reply; a mail problem never loses the lead (it's saved first).
 */
class NewLead extends Notification
{
    use Queueable;

    /** @param  array<string, ?string>  $details  label => value, shown as lines in the email */
    public function __construct(
        public string $kind,
        public array $details,
        public string $adminUrl,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->details['Name'] ?? $this->details['Email'] ?? 'someone';
        $mail = (new MailMessage)
            ->subject("New {$this->kind}: {$name}")
            ->greeting("New {$this->kind}");

        foreach (array_filter($this->details, fn ($value) => filled($value)) as $label => $value) {
            // Visitors type these values: escape Markdown so a message can't turn into a link or formatting in the email
            $text = addcslashes(str_replace(["\r", "\n"], ' ', (string) $value), '\\`*_{}[]()#+-.!|'); // < and > are escaped by the template
            $mail->line("**{$label}:** ".$text);
        }

        return $mail->action('Open in the admin', $this->adminUrl)
            ->salutation('Mark Properties website');
    }

    /**
     * Sends to config('site.leads_email') after the response, and only logs a failure: the lead is already saved.
     *
     * @param  array<string, ?string>  $details
     */
    public static function send(string $kind, array $details, string $adminUrl): void
    {
        $to = config('site.leads_email');
        if (! $to) {
            return;
        }

        dispatch(function () use ($to, $kind, $details, $adminUrl) {
            try {
                \Illuminate\Support\Facades\Notification::route('mail', $to)->notify(new self($kind, $details, $adminUrl));
            } catch (\Throwable $e) {
                report($e);
            }
        })->afterResponse();
    }
}
