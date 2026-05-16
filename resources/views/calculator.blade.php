<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stamp Duty Calculator</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,.1);
            padding: 2rem;
            width: 100%;
            max-width: 560px;
        }

        h1 { font-size: 1.4rem; font-weight: 700; margin-bottom: .25rem; }
        .subtitle { color: #666; font-size: .9rem; margin-bottom: 1.75rem; }

        label { display: block; font-size: .875rem; font-weight: 600; margin-bottom: .4rem; }

        .field { margin-bottom: 1.25rem; }

        .input-wrap { position: relative; }
        .input-wrap .prefix {
            position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
            color: #555; font-weight: 600; pointer-events: none;
        }
        input[type="number"], select {
            width: 100%;
            padding: .6rem .75rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            appearance: none;
        }
        input[type="number"] { padding-left: 1.75rem; }
        input[type="number"]:focus, select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37,99,235,.2);
        }

        .error-msg { color: #dc2626; font-size: .8rem; margin-top: .35rem; }

        button[type="submit"] {
            width: 100%;
            padding: .7rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: .25rem;
        }
        button[type="submit"]:hover { background: #1d4ed8; }
        button[type="submit"]:disabled { background: #93c5fd; cursor: not-allowed; }

        .result-box { margin-top: 1.75rem; border-top: 1px solid #e5e7eb; padding-top: 1.5rem; }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: .5rem;
        }
        .total-label { font-weight: 600; font-size: 1rem; }
        .total-amount { font-size: 1.6rem; font-weight: 700; color: #2563eb; }
        .effective-rate { font-size: .85rem; color: #555; margin-bottom: 1.25rem; }

        .ftb-notice {
            background: #fef9c3;
            border: 1px solid #fde047;
            border-radius: 5px;
            padding: .6rem .85rem;
            font-size: .82rem;
            margin-bottom: 1rem;
            color: #713f12;
        }

        .breakdown-title { font-size: .8rem; font-weight: 600; color: #555; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .6rem; }

        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead th {
            text-align: left;
            padding: .4rem .5rem;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #555;
            font-size: .8rem;
        }
        thead th:last-child { text-align: right; }
        tbody td { padding: .5rem .5rem; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        tbody td:last-child { text-align: right; white-space: nowrap; }
        tfoot td { padding: .6rem .5rem; font-weight: 700; border-top: 2px solid #e5e7eb; }
        tfoot td:last-child { text-align: right; }

        .api-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 5px;
            padding: .75rem 1rem;
            color: #dc2626;
            font-size: .875rem;
            margin-top: 1.25rem;
        }
    </style>
</head>
<body>

<div class="card" x-data="calculator()">

    <h1>Stamp Duty Calculator</h1>
    <p class="subtitle">Calculate the Stamp Duty Land Tax (SDLT) on a residential property purchase in England.</p>

    <form @submit.prevent="submit" novalidate>

        <div class="field">
            <label for="price">Purchase price</label>
            <div class="input-wrap">
                <span class="prefix">£</span>
                <input
                    id="price"
                    type="number"
                    min="1"
                    step="1"
                    placeholder="e.g. 295000"
                    x-model="price"
                    @input="clearResult"
                    :class="errors.price ? 'border-red' : ''"
                    style="border-color: errors.price ? '#dc2626' : ''"
                >
            </div>
            <p class="error-msg" x-show="errors.price" x-text="errors.price"></p>
        </div>

        <div class="field">
            <label for="scenario">Buyer type</label>
            <select id="scenario" x-model="scenario" @change="clearResult">
                <option value="standard">Standard buyer</option>
                <option value="first_time_buyer">First-time buyer</option>
                <option value="additional_property">Additional / buy-to-let property</option>
            </select>
            <p class="error-msg" x-show="errors.scenario" x-text="errors.scenario"></p>
        </div>

        <button type="submit" :disabled="loading">
            <span x-show="!loading">Calculate</span>
            <span x-show="loading">Calculating…</span>
        </button>

    </form>

    <div class="api-error" x-show="apiError" x-text="apiError"></div>

    <div class="result-box" x-show="result" x-cloak>

        <div class="total-row">
            <span class="total-label">Total Stamp Duty</span>
            <span class="total-amount" x-text="result?.total_pounds"></span>
        </div>
        <p class="effective-rate">
            Effective rate: <strong x-text="result?.effective_rate + '%'"></strong>
            of the purchase price
        </p>

        {{-- Notice when FTB relief was not applied because price exceeded cap --}}
        <div class="ftb-notice"
             x-show="requestedScenario === 'first_time_buyer' && result?.scenario_used === 'standard'">
            First-time buyer relief does not apply above £500,000. Standard rates have been used.
        </div>

        <p class="breakdown-title">How this is calculated</p>

        <table>
            <thead>
                <tr>
                    <th>Band</th>
                    <th style="text-align:right">Taxable amount</th>
                    <th style="text-align:right">Rate</th>
                    <th style="text-align:right">Tax</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="row in result?.breakdown" :key="row.label">
                    <tr>
                        <td x-text="row.label"></td>
                        <td x-text="row.taxable_pounds"></td>
                        <td x-text="row.rate_percent"></td>
                        <td x-text="row.tax_pounds"></td>
                    </tr>
                </template>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td x-text="result?.total_pounds"></td>
                </tr>
            </tfoot>
        </table>

    </div>

</div>

<script>
function calculator() {
    return {
        price: '',
        scenario: 'standard',
        loading: false,
        result: null,
        errors: {},
        apiError: '',
        requestedScenario: '',

        clearResult() {
            this.result   = null;
            this.errors   = {};
            this.apiError = '';
        },

        async submit() {
            this.errors   = {};
            this.apiError = '';
            this.result   = null;
            this.loading  = true;
            this.requestedScenario = this.scenario;

            try {
                const response = await fetch('/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        price:    this.price,
                        scenario: this.scenario,
                    }),
                });

                const data = await response.json();

                if (response.status === 422) {
                    this.errors = Object.fromEntries(
                        Object.entries(data.errors).map(([k, v]) => [k, v[0]])
                    );
                    return;
                }

                if (! response.ok) {
                    this.apiError = data.message ?? 'Something went wrong. Please try again.';
                    return;
                }

                this.result = data;

            } catch (e) {
                this.apiError = 'Application error. Please try again.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>

</body>
</html>
