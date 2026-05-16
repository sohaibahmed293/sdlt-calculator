# PROCESS.md

## How long this actually took

- Reading the brief, researching current SDLT rules on HMRC, and planning the architecture:  approx 45 mins
- Environment setup (PHP, Composer, Laravel): 5 mins
- Building the app (config, service, controller, view):  approx 1 hr 30 mins
- Tests, running the suite, and verifying against HMRC: 30 mins
- README and PROCESS.md: 20 mins
- **Total: approximately 3 hours 10 minutes**

---

## How I used AI tooling

I used Claude Code CLI as a scaffolding assistant for setting up the project, things like generating the initial file structure, writing the Blade view markup, and producing a first draft of the test suite. The architecture decisions, SDLT rate research, config structure, and all verification were done manually.

**One specific example of output I rejected and rewrote:**

The AI defined the rate band thresholds in config with a +1 offset on each lower boundary, so the second band started at 12500001 pence instead of 12500000. The tax totals came out correct because the 1-penny rounding difference cancels out, but the per-band taxable amounts in the breakdown were always 1p short: £124,999 instead of £125,000. I caught it while manually checking the breakdown table against HMRC and tracing back to where the taxable slice was being calculated. Fixed the config to use shared boundaries so each band's from equals the previous band's to, which is how the progressive tax arithmetic is supposed to work.

---

## How I verified the maths

I ran each of the main scenarios through HMRC's own calculator at https://www.tax.service.gov.uk/calculate-stamp-duty-land-tax and compared the results. For each one I selected: residential property, freehold, UK resident, and set the effective date to after 1 April 2025 so it uses the current rate bands that came back into force when the temporary nil-rate increase ended.

The numbers matched across all four examples:

| Purchase price | Buyer type | Our result | HMRC result |
|---|---|---|---|
| £295,000 | Standard | £4,750 | £4,750 |
| £400,000 | First-time buyer | £5,000 | £5,000 |
| £600,000 | First-time buyer | £20,000 | £20,000 |
| £295,000 | Additional property | £19,500 | £19,500 |

One thing worth noting for the £600,000 first-time buyer case: HMRC applies standard rates in full when the price is above £500,000, with no partial relief. The calculator handles this correctly and shows a notice to the user explaining why FTB relief has not been applied.

---

## What I'd do with another hour

- Add band-boundary test cases for the higher thresholds (£925,000, £1,500,000)
- Display more specific validation feedback so the user knows why their input was rejected rather than seeing a generic error
- Preserve the last result on screen when the user edits the inputs, so they can compare before and after without losing the previous figure
