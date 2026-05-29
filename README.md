# SplitBill

> A bill-splitting web app for Indonesian groups — friends, groups, expenses,
> settlements, and receipt OCR. Rupiah-first, mobile-first.

![tests](https://github.com/fabianokky/splitbill/actions/workflows/tests.yml/badge.svg)
![lint](https://github.com/fabianokky/splitbill/actions/workflows/lint.yml/badge.svg)
![license](https://img.shields.io/badge/license-MIT-blue.svg)

SplitBill is a Splitwise-style web app built for Indonesian users. It is
mobile-first, Rupiah-only (integer storage, no floats), and ships a small Python
OCR microservice that reads photographed receipts (Indonesian _struk_) so users
can prefill an expense from a receipt instead of typing every line.

The Laravel backend is exposed both to a Livewire web UI and — through the same
shared Action classes — to a Sanctum-protected JSON API ready for a future
Flutter client.

## Features

- **Friends** — every user gets a public 8-char friend code; add by code, send/accept/
  decline requests, auto-accept on reciprocal pending.
- **Groups & expenses** — create a group, add members, log expenses with three split
  methods (equal / exact / percent). Money is always integer rupiah; split remainders
  are assigned deterministically.
- **Balances & settlements** — per-member net balance and a "minimal transfers" list
  (who pays whom) per group. Settlements are append-only.
- **Receipt OCR** — upload a photo of a receipt; the Python service (Tesseract 5
  + `ind`) parses the total and line items in Indonesian receipt format.
  Degrades gracefully if the service is offline.
- **Activity feed & in-app notifications** — every meaningful change posts an event
  to the group's activity panel and the user's bell.
- **JSON API** — Sanctum bearer tokens, versioned at `/api/v1`, throttled. See
  [`docs/api.md`](docs/api.md).

## Screenshots

> Will be added once the public demo is up. Placeholder filenames are stable so the
> README updates as the screenshots land.

| | |
|---|---|
| Dashboard — [`docs/screenshots/dashboard.png`](docs/screenshots/dashboard.png) | Group — [`docs/screenshots/group.png`](docs/screenshots/group.png) |
| Add expense w/ OCR — [`docs/screenshots/expense-ocr.png`](docs/screenshots/expense-ocr.png) | Settle up — [`docs/screenshots/settle.png`](docs/screenshots/settle.png) |

## Tech stack

| Layer | Choice |
|---|---|
| Backend | PHP 8.4, Laravel 13 |
| Web UI | Livewire 4 + Flux UI Free + Tailwind CSS 4 |
| Auth | Laravel Fortify (email + password) |
| API | Laravel Sanctum (bearer tokens) |
| Tests | Pest 4 (Feature + Unit + browser) |
| OCR | Python 3.11 + FastAPI + Tesseract 5 (`ind` language pack), swappable to RapidOCR |
| Database | MySQL by default (SQLite for tests; MariaDB-ready via `.env`) |
| Code style | Laravel Pint |

## Architecture

Business logic lives in plain PHP **Action classes** so the web UI (Livewire) and
the JSON API (Sanctum) share the same code path. Money is stored as integer minor
units (rupiah). The OCR service is a separate process on `127.0.0.1:8001` that
Laravel calls over HTTP only — Python is never imported in PHP.

```
Browser / Flutter --> Laravel (Livewire + API) --> Actions --> Eloquent --> MySQL
                                                       |
                                                       +--> HTTP --> FastAPI (Tesseract)
```

Full diagram: [`ARCHITECTURE.svg`](ARCHITECTURE.svg).
Production runbook: [`docs/deploy.md`](docs/deploy.md).

## Local setup

> Full step-by-step guide — including prerequisites table, troubleshooting, and
> a cheat sheet of daily Artisan commands — lives at
> [`docs/getting-started.md`](docs/getting-started.md). The recipe below is the
> short version for someone who already knows Laravel.

You need Laravel Herd (PHP 8.4 + Composer), Node.js LTS, Git, Python 3.11+, and
Tesseract 5 with the **Indonesian** language pack (`tesseract --list-langs` must
show `ind`).

```powershell
# Clone & install
git clone https://github.com/fabianokky/splitbill.git
cd splitbill
composer install
npm install
copy .env.example .env
php artisan key:generate

# Database (MySQL by default; SQLite also works via DB_CONNECTION=sqlite)
php artisan migrate
php artisan storage:link

# OCR microservice (separate terminal)
cd ocr-service
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
uvicorn main:app --host 127.0.0.1 --port 8001
```

Back in the repo root, run the web app + queue + Vite together:

```powershell
composer run dev
# Laravel: http://localhost:8000
```

Smoke-test the OCR boundary:

```powershell
php artisan ocr:health           # exits 0 when the Python service is up
php artisan ocr:scan tests/Fixtures/receipts/<your-file>.jpg
```

## Running tests

```powershell
# Laravel (Pest + Pint)
php artisan test --compact
.\vendor\bin\pint --test

# OCR service (pytest, in ocr-service/)
pytest
```

The integration spec at `tests/Feature/Expenses/ScanReceiptIntegrationTest.php`
automatically skips unless the Python service is reachable, so CI stays green
without it.

## API

Versioned JSON API under `/api/v1`, secured with Sanctum bearer tokens, throttled
per route group. The public web UI and the API share the same Action classes, so
parity is automatic. See [`docs/api.md`](docs/api.md) for the endpoint list and
example requests.

## Roadmap

- Multi-payer expenses (group buys where two or more people chip in for the
  same purchase).
- Edit / undo settlements (compensating records, append-only audit trail).
- Group archive / leave / delete with balance checks.
- Expense categories + insights.
- 2FA / passkeys before public launch.

Lower-priority backlog: multi-currency, push notifications, recurring expenses,
CSV export, guest members, i18n (id / en).

## License

[MIT](LICENSE) © 2026 Fabian Okky.
