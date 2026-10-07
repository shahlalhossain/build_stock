<?php

namespace App\Http\Requests\NotificationTemplate;

use App\Services\Notification\TemplateRenderer;
use App\Services\NotificationTemplateService;
use Illuminate\Validation\Validator;

/**
 * Shared checks for the "variables" list and the {{placeholders}} used in the text.
 */
trait ChecksTemplatePlaceholders
{
    /**
     * Runs after the normal rules. Stops if the variable names are bad or
     * if the text uses a {{placeholder}} that is not in the variables list.
     */
    public function checkPlaceholders(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $listed = NotificationTemplateService::splitVariableNames($this->input('variables'));

        $badNames = array_filter($listed, fn (string $name) => ! preg_match('/^[a-z0-9_]+$/', $name));
        if ($badNames !== []) {
            $validator->errors()->add('variables', __('Variable names may only use small letters, digits and underscore. Wrong: :names', ['names' => implode(', ', $badNames)]));

            return;
        }

        if ($listed === []) {
            return;
        }

        $unknown = $this->unknownPlaceholders($listed);
        if ($unknown !== []) {
            $validator->errors()->add('body', __('These placeholders are not in the Variables list: :names', ['names' => implode(', ', $unknown)]));
        }
    }

    /**
     * Returns the placeholder names used in subject, title or body that are neither in the listed
     * variables nor one of the standard ones (actor_name, actor_email, created_at, url).
     */
    protected function unknownPlaceholders(array $listed): array
    {
        $text = implode(' ', [$this->input('subject'), $this->input('title'), $this->input('body')]);

        preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $text, $matches);

        return array_values(array_diff(array_unique($matches[1]), $listed, TemplateRenderer::STANDARD_VARIABLES));
    }
}
