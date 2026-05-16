# SDLT Calculator

A standalone Stamp Duty Land Tax (SDLT) calculator for residential property purchases in England. Supports standard rates, first-time buyer relief, and the additional property surcharge.

---

## Requirements

- PHP 8.2 or higher
- Composer

> **On a fresh Mac with Homebrew:**
> ```bash
> brew install php composer
> ```

---

## Getting started

```bash
# 1. Clone the repository
git clone <repository-url>
cd sdlt-calculator

# 2. Install dependencies
composer install

# 3. Copy the environment file
cp .env.example .env

# 4. Generate the application key
php artisan key:generate

# 5. Start the development server
php artisan serve
```

Open your browser at **http://localhost:8000**

No database setup, no build step, no Docker required.

---

## Running the tests

```bash
php artisan test
```

---

## Example inputs and expected outputs

| Purchase price | Buyer type | Expected SDLT |
|---|---|---|
| £295,000 | Standard buyer | **£4,750** |
| £400,000 | First-time buyer | **£5,000** |
| £600,000 | First-time buyer | **£20,000** (FTB relief does not apply above £500,000; standard rates used) |
| £295,000 | Additional / buy-to-let property | **£19,500** |
