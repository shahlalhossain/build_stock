<?php

namespace Tests\Feature;

use App\Exceptions\GeneralException;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\AttributeValueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeValueSyncTest extends TestCase
{
    use RefreshDatabase;

    protected AttributeValueService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AttributeValueService::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_adding_a_value_keeps_existing_values_ids_unchanged(): void
    {
        $attribute = Attribute::factory()->create();
        $this->service->syncValues($attribute, ['Cement', 'Brick']);

        $existingIds = $attribute->values()->pluck('id', 'value');

        $this->service->syncValues($attribute, ['Cement', 'Brick', 'Sand']);

        $afterIds = $attribute->values()->pluck('id', 'value');

        $this->assertEquals($existingIds['Cement'], $afterIds['Cement']);
        $this->assertEquals($existingIds['Brick'], $afterIds['Brick']);
        $this->assertTrue($afterIds->has('Sand'));
        $this->assertCount(3, $afterIds);
    }

    public function test_removing_an_unused_value_only_deletes_that_value(): void
    {
        $attribute = Attribute::factory()->create();
        $this->service->syncValues($attribute, ['Cement', 'Brick', 'Sand']);

        $beforeIds = $attribute->values()->pluck('id', 'value');

        $this->service->syncValues($attribute, ['Cement', 'Sand']);

        $afterIds = $attribute->values()->pluck('id', 'value');

        $this->assertFalse($afterIds->has('Brick'));
        $this->assertEquals($beforeIds['Cement'], $afterIds['Cement']);
        $this->assertEquals($beforeIds['Sand'], $afterIds['Sand']);
        $this->assertCount(2, $afterIds);
    }

    public function test_removing_a_value_referenced_by_a_product_variant_fails_without_touching_other_values(): void
    {
        $attribute = Attribute::factory()->create();
        $this->service->syncValues($attribute, ['Cement', 'Brick']);

        $cementValue = $attribute->values()->where('value', 'Cement')->firstOrFail();
        $brickValueId = $attribute->values()->where('value', 'Brick')->value('id');

        $variant = ProductVariant::factory()->create();
        $variant->attributeValues()->attach($cementValue->id);

        $this->expectException(GeneralException::class);

        try {
            // Removing "Cement" (Referenced by a Variant) while also Adding "Sand".
            $this->service->syncValues($attribute, ['Brick', 'Sand']);
        } finally {
            // "Brick" (untouched, unreferenced) must survive even though the
            // whole sync call failed — confirms the diff Approach only ever
            // touches the specific Row that Violates the Foreign Key, not the
            // Attribute's other Values.
            $this->assertTrue(AttributeValue::query()->where('id', $brickValueId)->exists());
            $this->assertTrue(AttributeValue::query()->where('id', $cementValue->id)->exists());
        }
    }

    public function test_unrelated_attribute_values_are_untouched_by_an_update(): void
    {
        $attributeA = Attribute::factory()->create();
        $attributeB = Attribute::factory()->create();

        $this->service->syncValues($attributeA, ['Small', 'Large']);
        $this->service->syncValues($attributeB, ['Red', 'Blue']);

        $attributeBIdsBefore = $attributeB->values()->pluck('id', 'value');

        $this->service->syncValues($attributeA, ['Small', 'Large', 'Medium']);

        $attributeBIdsAfter = $attributeB->values()->pluck('id', 'value');

        $this->assertEquals($attributeBIdsBefore->all(), $attributeBIdsAfter->all());
    }
}
