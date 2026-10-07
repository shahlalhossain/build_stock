<?php

namespace App\Services\Notification;

use App\Models\NotificationReceiver;
use App\Models\NotificationTemplate;

/**
 * Fills a message text such as "Brand {{brand_name}} was created by {{actor_name}}"
 * with real values.
 *
 * Only simple {{name}} placeholders are replaced. No PHP code is ever run.
 * A placeholder that is not allowed, or has no value, becomes an empty text.
 */
class TemplateRenderer
{
    /**
     * Placeholders that every template may always use.
     */
    public const STANDARD_VARIABLES = ['actor_name', 'actor_email', 'created_at', 'url'];

    /**
     * Replaces the placeholders in one text.
     *
     * @param  array<string, mixed>  $data  Values by placeholder name.
     * @param  array<int, string>  $allowedVariables  Extra placeholder names allowed besides the standard ones.
     */
    public function render(?string $text, array $data, array $allowedVariables = []): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $allowed = array_merge(self::STANDARD_VARIABLES, $allowedVariables);

        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function (array $match) use ($data, $allowed) {
            $name = $match[1];

            if (! in_array($name, $allowed, true)) {
                return '';
            }

            return $this->valueAsText($data[$name] ?? null);
        }, $text);
    }

    /**
     * Renders the subject, title and body of a template in one go.
     *
     * @param  array<string, mixed>  $data
     * @return array{subject: string, title: string, body: string}
     */
    public function renderTemplate(NotificationTemplate $template, array $data): array
    {
        $allowed = $template->variables ?? [];

        return [
            'subject' => $this->render($template->subject, $data, $allowed),
            'title' => $this->render($template->title, $data, $allowed),
            'body' => $this->render($template->body, $data, $allowed),
        ];
    }

    /**
     * Works out the final text one receiver should get on its channel.
     * Uses the channel's own template when the notification came from a setting.
     * Otherwise (for example manual notifications) it uses the text saved on the notification.
     *
     * @return array{subject: string, title: string, body: string}
     */
    public function renderForReceiver(NotificationReceiver $receiver): array
    {
        $message = $receiver->message;

        $template = $message->notification_setting_id === null
            ? null
            : NotificationTemplate::active()
                ->where('notification_setting_id', $message->notification_setting_id)
                ->where('channel_id', $receiver->channel_id)
                ->first();

        if ($template === null) {
            return [
                'subject' => (string) $message->title,
                'title' => (string) $message->title,
                'body' => (string) $message->message_body,
            ];
        }

        return $this->renderTemplate($template, $message->data ?? []);
    }

    /**
     * Turns a value into safe plain text. Lists and objects become empty text.
     */
    protected function valueAsText(mixed $value): string
    {
        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }

        return '';
    }
}
