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

## Roadmap

- [ ] Phase 1: Anonymous text posting ("Drop")
- [ ] Phase 2: PoW captcha (server-side Hashcash, no JS)
- [ ] Phase 3: Cryptographic deletion (passphrases to delete your own posts)
- [ ] Phase 4: maybe `.onion` hidden service setup

## Quick Privacy Site

A Reddit-style anonymous link aggregator in `/public/`:

```bash
# Install PHP and Apache
sudo apt-get install php libapache2-mod-php

# Enable mod_rewrite
sudo a2enmod rewrite

# Copy config
sudo cp privacy-site.apache.conf /etc/apache2/sites-available/
sudo a2ensite privacy-site
sudo systemctl reload apache2

# Or run with PHP built-in server
cd public && php -S localhost:8000
```

Then open http://localhost
