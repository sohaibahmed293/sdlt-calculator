<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalculatorTest extends TestCase
{
    #[Test]
    public function calculator_page_loads(): void
    {
        $this->get('/')->assertStatus(200)->assertSee('Stamp Duty');
    }

    #[Test]
    public function calculate_returns_correct_result_for_standard_rate(): void
    {
        // £295,000 standard → £4,750
        $this->postJson('/calculate', ['price' => 295000, 'scenario' => 'standard'])
            ->assertStatus(200)
            ->assertJson(['total_pence' => 475000]);
    }

    #[Test]
    public function calculate_rejects_zero_price(): void
    {
        $this->postJson('/calculate', ['price' => 0, 'scenario' => 'standard'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    #[Test]
    public function calculate_rejects_invalid_scenario(): void
    {
        $this->postJson('/calculate', ['price' => 200000, 'scenario' => 'unknown'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['scenario']);
    }
}
