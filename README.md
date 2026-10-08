# Malezi na Watoto — Website

A full PHP / MySQL website with an admin panel for **Malezi na Watoto** (Wakulima Agri-Food Company Ltd, Mwanza, Tanzania) — a community-based organization improving the well-being, development, and future of young children and their families. Bilingual (English + Kiswahili) with a Tanzania-flag inspired palette.

Partner: [Nadace Maendeleo (maendeleo.cz)](https://maendeleo.cz/) — linked from the site footer.

## What's inside

- **Public site** — Home, About, What We Do, Programs, News, Gallery, Contact
- **Bilingual UI** — EN / SW toggle with minimal flag icons (all UI strings in `lang/en.php` and `lang/sw.php`)
- **Admin panel** — Login, dashboard, CRUD for posts, programs, gallery, members, admin users, messages, newsletter, and site settings
- **Gallery** — Multi-upload, categories, [PhotoSwipe v5](https://photoswipe.com/) lightbox with caption bar and thumbnail filmstrip
- **Contact form** — Saves to database, visible in admin, CSRF-protected, optional reCAPTCHA v3
- **Messages inbox** — Filter tabs (All / Unread / Read), full-text search, sender history, quoted-reply mailto
- **Newsletter** — Double opt-in style subscription form in the footer, admin list with unsubscribe tokens
- **SEO** — Canonical URLs, hreflang alternates, Open Graph + Twitter cards, JSON-LD (Organization + WebSite), sitemap.xml generation
- **Integrations** — reCAPTCHA v3, Google Analytics 4 (both configurable from admin → Settings)
- **MySQL** via PDO with prepared statements
- **Responsive** design, mobile navigation drawer, sticky header, HTTPS-aware asset URLs

## Requirements

- PHP 7.4+ (works on 8.x)
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite`, `mod_headers`, `mod_deflate` (recommended)

## Install

1. Copy the whole `wakulima` folder to your web server (e.g. `htdocs/wakulima`).
2. Edit `includes/config.php` and set:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - `SITE_URL` (no trailing slash, e.g. `https://wakulima.example.com`) — scheme is auto-detected per request so HTTP/HTTPS both work behind reverse proxies.
   - Optional: `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, `GA_MEASUREMENT_ID` (or set these later from admin → Settings)
3. Open `http://your-host/wakulima/install.php` in a browser.
4. Choose an admin username, email and password → **Install**.
5. Delete `install.php` from the server for security.
6. Log into the admin at `http://your-host/wakulima/admin/`.
7. (Optional) Run `install/mwanza-address.sql` to seed the Mwanza contact info, or edit values under admin → Settings.

## File map

```
/
├── index.php              home
├── about.php
├── what-we-do.php
├── programs.php
├── news.php
├── news-single.php
├── gallery.php
├── contact.php
├── newsletter-subscribe.php
├── newsletter-unsubscribe.php
├── sitemap.php            (dynamic; regenerate to sitemap.xml from admin)
├── robots.txt
├── 404.php
├── install.php            (delete after install)
├── .htaccess
│
├── admin/
│   ├── index.php          login
│   ├── dashboard.php
│   ├── posts.php · post-edit.php
│   ├── programs.php · program-edit.php
│   ├── gallery.php
│   ├── members.php · member-edit.php
│   ├── messages.php       inbox: filter tabs, search, quoted reply
│   ├── newsletter.php
│   ├── users.php
│   ├── settings.php       site info, integrations, sitemap regen
│   ├── logout.php
│   └── includes/          auth, admin header/footer
│
├── install/
│   ├── database.sql       schema + default settings
│   └── mwanza-address.sql upsert Mwanza contact / phone / emails
│
├── includes/              config, db, functions, header, footer
├── lang/                  en.php, sw.php  (all UI strings)
├── uploads/               generated media (gallery, posts, programs, members)
└── assets/
    ├── css/style.css      public site
    ├── css/admin.css      admin panel
    ├── js/main.js
    └── images/            logo files
```

## Adding a new language

1. Copy `lang/en.php` to `lang/xx.php` and translate the values.
2. In `includes/functions.php`, `current_lang()`, add `'xx'` to the allowed list.
3. In `includes/header.php`, add another `<a>` in `.lang-switch` for that code with an inline SVG flag.

## Security notes

- All database queries use PDO prepared statements.
- CSRF tokens on every state-changing form.
- Passwords hashed with `password_hash()` (bcrypt).
- Optional reCAPTCHA v3 on contact + newsletter forms (min score configurable).
- `/uploads/`, `/includes/`, `/lang/`, `/admin/includes/` protected via `.htaccess`.
- Uploads restricted to JPG/PNG/WEBP/GIF, max 5 MB, MIME-checked, safe filenames.
- Newsletter unsubscribe uses unguessable per-subscriber token.

## Design

Palette — Tanzania flag inspired:
- Green: `#127A27` (deep), `#17962F` (brand), `#1EB53A` (light)
- Gold: `#FCD116`
- Red: `#CE1126`
- Sky: `#00A3DD`
- Text/UI: warm dark `#0C0C0C`, cream page background

Typography (loaded from Google Fonts in the header):
- Display: **Bodoni Moda**
- UI / headings: **Josefin Sans**
- Body: **Figtree**

## Gallery lightbox

Uses [PhotoSwipe v5](https://photoswipe.com/) loaded from jsDelivr CDN, only on pages that set `$hasGallery = true` (home preview and the gallery page). Custom UI elements register in `includes/footer.php`:
- Caption bar reads from each item's `.caption` element
- Thumbnail filmstrip along the bottom, auto-scrolls to current slide, click to jump

Image dimensions are resolved server-side via `getimagesize()` (see `image_dimensions()` in `includes/functions.php`).

## Contact / map

Contact page reads `contact_map_query` from settings and embeds a Google Maps iframe for that query. On `contact.php` the multi-line `contact_address` renders with `nl2br` (so you can put the company name on line 1 and city on line 2) and both `contact_email` and `contact_email_2` are shown when set.
