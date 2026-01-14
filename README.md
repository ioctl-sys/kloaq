# KLOQ

A JavaScript-free anonymous publishing platform.

- Domain: kloq.net
- Stack: PHP 8.2 + Nginx + SQLite
- License: MIT
- Status: Alpha

## Mission

The modern web is a surveillance engine. KLOQ is the antidote.

KLOQ is a purely server-side publishing platform built for anonymity by default:

- No JavaScript
- No cookies
- No third-party requests

By removing the client-side execution layer, we remove a major source of fingerprinting and de-anonymization. If your browser can render HTML, you can publish.

## Principles

### Zero JavaScript
KLOQ works fully without client-side scripting.

Tor Browser users can run **Safest** mode without losing functionality.

### Minimal data collection
KLOQ is designed to avoid collecting identifying metadata:

- No IP address logging
- No User-Agent logging
- No tracking pixels
- No third-party assets

### Policy enforcement (without device tracking)
We enforce rules by acting on accounts and content, not by tracking devices.

If an account violates policy, that account gets banned. If someone returns with a new account, enforcement repeats.

### Minimalist stack
Built with raw PHP (no framework), optimized for fast rendering on high-latency networks (Tor/I2P).

### Ephemeral by design
Content lives in RAM only - posts and comments disappear on server restart. Only encrypted user accounts persist to disk. See [STORAGE.md](STORAGE.md) for details.

## Roadmap

- [ ] Phase 1: Anonymous text posting ("Drop")
- [ ] Phase 2: PoW captcha (server-side Hashcash, no JS)
- [ ] Phase 3: Cryptographic deletion (passphrases to delete your own posts)
- [ ] Phase 4: maybe `.onion` hidden service setup

## Quick Start

### Simple method (run.sh)
```bash
./run.sh
```

### Or manually with PHP built-in server
```bash
cd public && php -S localhost:8000
```

### Apache setup (production)
```bash
# Install PHP and Apache
sudo apt-get install php libapache2-mod-php

# Enable mod_rewrite
sudo a2enmod rewrite

# Copy config
sudo cp privacy-site.apache.conf /etc/apache2/sites-available/
sudo a2ensite privacy-site
sudo systemctl reload apache2
```

Then open http://localhost:8000

### Storage Configuration

Set encryption key for user accounts database:
```bash
export KLOAQ_DB_KEY="$(openssl rand -base64 32)"
./run.sh
```

**Important:** All posts/comments are stored in RAM and will be lost on restart. Only user accounts persist. See [STORAGE.md](STORAGE.md) for the full architecture.
