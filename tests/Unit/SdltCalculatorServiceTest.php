<?php

namespace Tests\Unit;

use App\Enums\Scenario;
use App\Services\SdltCalculatorService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SdltCalculatorServiceTest extends TestCase
{
    private SdltCalculatorService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new SdltCalculatorService();
    }

    #[Test]
    public function standard_rate_at_nil_rate_band_boundary_pays_zero(): void
    {
        $result = $this->svc->calculate(12500000, Scenario::Standard); // £125,000
        $this->assertSame(0, $result['total_pence']);
    }

    #[Test]
    public function standard_rate_spans_three_bands_at_295k(): void
    {
        // £295,000: 0% on £125k + 2% on £125k + 5% on £45k = £0 + £2,500 + £2,250 = £4,750
        $result = $this->svc->calculate(29500000, Scenario::Standard);
        $this->assertSame(475000, $result['total_pence']);
    }

    #[Test]
    public function ftb_rate_applies_below_price_cap(): void
    {
        // £400,000: 0% on £300k + 5% on £100k = £5,000
        $result = $this->svc->calculate(40000000, Scenario::FirstTimeBuyer);
        $this->assertSame(500000, $result['total_pence']);
        $this->assertSame(Scenario::FirstTimeBuyer->value, $result['scenario_used']);
    }

    #[Test]
    public function ftb_above_500k_falls_back_to_standard_rates(): void
    {
        // £600,000: no FTB relief — standard rates: 0% on £125k + 2% on £125k + 5% on £350k = £20,000
        $result = $this->svc->calculate(60000000, Scenario::FirstTimeBuyer);
        $this->assertSame(2000000, $result['total_pence']);
        $this->assertSame(Scenario::Standard->value, $result['scenario_used']);
    }

    #[Test]
    public function additional_property_surcharge_applies_to_nil_rate_band(): void
    {
        // £100,000: 0% standard tax + 5% surcharge on full price = £5,000
        $result = $this->svc->calculate(10000000, Scenario::AdditionalProperty);
        $this->assertSame(500000, $result['total_pence']);
    }

    #[Test]
    public function negative_price_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->svc->calculate(-1, Scenario::Standard);
    }
}
