<?php

namespace App\Http\Controllers;

use App\Enums\Scenario;
use App\Http\Requests\CalculateRequest;
use App\Services\SdltCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CalculatorController extends Controller
{
    public function __construct(private SdltCalculatorService $calculator) {}

    public function index(): View
    {
        return view('calculator');
    }

    public function calculate(CalculateRequest $request): JsonResponse
    {
        $result = $this->calculator->calculate(
            $request->pricePence(),
            Scenario::from($request->validated('scenario'))
        );

        return response()->json($result);
    }
}
