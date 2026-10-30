# The Trust Trap: Navigating Digital Deception

Guardian is a Laravel digital-safety app for checking suspicious links and files and asking questions about online scams.

## Features

- **Chatbot:** Uses Hugging Face Inference Providers for digital-safety guidance. It has no local scripted-answer fallback.
- **Link checker:** Checks one HTTP(S) URL with VirusTotal after the user consents.
- **File scanner:** Scans files up to 10 MB with OPSWAT MetaDefender after the user consents. Sample sharing is disabled in the request.
- **Responsive interface:** Mobile and desktop layouts, animated mascot, radial tool menu, and a Three.js background.

## Requirements

- PHP 8.2+ with `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_sqlite`, `xml`, and `zip` extensions
- Composer 2
- The PHP ZIP extension or an installed `unzip`/7-Zip utility to extract Composer packages

## Install and run

In PowerShell, from the repository root:

```powershell
Set-Location .\guardian-laravel
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000). The home page redirects to the Links tool.

## Configure API keys

Add your provider credentials to `guardian-laravel/.env`. Never put API keys in Blade templates, JavaScript, or files committed to Git. The `.env` file is ignored by Git. After changing environment values when config is cached, run this from `guardian-laravel`:

```powershell
php artisan config:clear
```

### Hugging Face chatbot

Create a Hugging Face access token with permission to call inference providers:

```dotenv
HUGGINGFACE_API_TOKEN=hf_your_token_here
```

Optional endpoint and model settings (already present in `.env.example`):

```dotenv
HUGGINGFACE_API_URL=https://router.huggingface.co/v1/chat/completions
HUGGINGFACE_MODEL=Qwen/Qwen2.5-7B-Instruct
```

The browser sends questions to `POST /api/tanya`; Laravel calls Hugging Face server-side, keeping the token out of the browser. Provider/model availability, pricing, and free quotas depend on your account and may change.

### VirusTotal link checks

```dotenv
VIRUSTOTAL_API_KEY=your_virustotal_key_here
```

The Links page requires explicit consent. Submitted or queried URLs may be added to VirusTotal's dataset; do not check private, personal, confidential, or one-time URLs. The public API has strict quotas and terms, and may not be used in commercial products or business workflows without the appropriate permission. Confirm current [API quotas and terms](https://docs.virustotal.com/reference/public-vs-premium-api) before deployment. A result with no detections is not a guarantee of safety.

### OPSWAT file checks

```dotenv
OPSWAT_API_KEY=your_opswat_key_here
```

Files are transmitted to OPSWAT MetaDefender for analysis with `samplesharing: 0`; Guardian does not keep a permanent copy. Do not upload sensitive, personal, or confidential files. Confirm the provider's current availability, quotas, retention policy, and terms before deployment. Scan results are not a guarantee that a file is safe.

The chatbot is separate from both scanners: links and files are not sent through the chatbot.

## Pages and API routes

| Page | Route | Description |
| --- | --- | --- |
| Links | `/links` | Check a URL with VirusTotal |
| Files | `/files` | Scan a file with OPSWAT |
| Chatbot | `/tanya` | Ask Hugging Face for digital-safety guidance |

| API endpoint | Description |
| --- | --- |
| `POST /api/links` | Submit a URL for checking |
| `POST /api/links/analysis` | Refresh a URL analysis |
| `POST /api/files` | Submit a file for scanning |
| `POST /api/files/analysis` | Refresh a file analysis |
| `POST /api/tanya` | Send a chatbot question |

## Run tests

From `guardian-laravel`:

```powershell
composer test
```

## Project structure

```text
guardian-laravel/
├── app/                 # Controllers and external-service integrations
├── config/              # Laravel and Guardian configuration
├── public/              # CSS, JavaScript, and web entry point
├── resources/views/     # Shared layout and tool pages
├── routes/web.php       # Web pages and API endpoints
├── tests/               # Feature tests
└── .env.example         # Environment-variable template (no secrets)
```
