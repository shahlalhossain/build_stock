<?php

namespace Tests\Feature\Notification;

use App\Models\NotificationTemplate;
use App\Services\Notification\TemplateRenderer;
use Tests\TestCase;

class TemplateRendererTest extends TestCase
{
    public function test_placeholders_are_replaced_with_values(): void
    {
        $text = (new TemplateRenderer)->render(
            'Brand {{brand_name}} has been created by {{actor_name}}.',
            ['brand_name' => 'ABC Cement', 'actor_name' => 'John'],
            ['brand_name']
        );

        $this->assertSame('Brand ABC Cement has been created by John.', $text);
    }

    public function test_spaces_inside_the_braces_are_allowed(): void
    {
        $text = (new TemplateRenderer)->render('Hi {{ actor_name }}', ['actor_name' => 'Ann']);

        $this->assertSame('Hi Ann', $text);
    }

    public function test_a_placeholder_that_is_not_allowed_becomes_empty(): void
    {
        $text = (new TemplateRenderer)->render('Secret: {{password}}.', ['password' => 'hunter2']);

        $this->assertSame('Secret: .', $text);
    }

    public function test_a_missing_value_becomes_empty_instead_of_failing(): void
    {
        $text = (new TemplateRenderer)->render('By {{actor_name}} at {{url}}', ['actor_name' => 'Ann']);

        $this->assertSame('By Ann at ', $text);
    }

    public function test_php_code_and_arrays_are_never_run_or_printed(): void
    {
        $renderer = new TemplateRenderer;

        $this->assertSame('<?php echo 1; ?>', $renderer->render('<?php echo 1; ?>', []));
        $this->assertSame('x', $renderer->render('x{{actor_name}}', ['actor_name' => ['a', 'b']]));
    }

    public function test_a_whole_template_is_rendered_with_its_own_variables(): void
    {
        $template = new NotificationTemplate([
            'subject' => 'New Brand',
            'title' => 'Brand Created',
            'body' => '{{brand_name}} by {{actor_name}}',
            'variables' => ['brand_name'],
        ]);

        $result = (new TemplateRenderer)->renderTemplate($template, ['brand_name' => 'ABC', 'actor_name' => 'Jo']);

        $this->assertSame(['subject' => 'New Brand', 'title' => 'Brand Created', 'body' => 'ABC by Jo'], $result);
    }
}
