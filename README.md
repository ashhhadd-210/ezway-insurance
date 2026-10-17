# EZ Way Insurance website

A custom-coded website for EZ Way Insurance / Aseguranza (client: hctor24). It has 4 pages, a Progressive-style multi-step quote form, and a password-protected dashboard. It runs on PHP and MySQL, which HostGator shared hosting supports out of the box. There is no framework and no build step.

```
website/
├── public_html/        ← upload the CONTENTS of this folder to HostGator's public_html
│   ├── index.php       Home
│   ├── about.php       About
│   ├── auto-insurance.php  Personal & commercial auto, coverages, FAQ (coverage.php redirects here)
│   ├── services.php    Home, business & contractors, bonds, title & registration, LLC/DBA
│   ├── contact.php     Contact + message form
│   ├── quote.php       Full-page quote form (the pop-up version is on every page)
│   ├── privacy.php     Privacy policy (template – have it reviewed)
│   ├── admin/          Password-protected dashboard
│   ├── api/            quote.php, contact.php, vin.php (form endpoints)
│   ├── app/            config.php, code, page partials (blocked from the web)
│   ├── assets/         css, js, img
│   └── storage/        database (if SQLite), encryption key, logs (blocked from the web)
├── api/index.php       Vercel entry point (Vercel preview only, not uploaded to HostGator)
├── vercel.json         Vercel config (Vercel preview only)
├── dev-router.php      local preview only (not uploaded)
└── tools/              HTML used to generate og-image.png and apple-touch-icon.png
```

## What the client asked for, and where it lives

| Request from the chat | Where |
|---|---|
| 4 fast, mobile-friendly pages with his logo/branding | Home, About, Coverage, Contact. Logo is set in `app/config.php` (`site.logo`) |
| Floating "Quote now" / "Start your quote" on every page | `.fab` button in `app/partials/footer.php`. Opens the quote pop-up on every page |
| Form similar to the Progressive link | `app/partials/quote-wizard.php` + `assets/js/quote.js`. 6 steps: You → Address → Drivers → Vehicles → Coverage → Review |
| Start with name and info | Step 1 (name, date of birth, phone, email, best way to reach you) and Step 2 (address, with city and state filled in from the ZIP) |
| "+" to add additional drivers | Step 3. Up to 8 drivers. Driver 1 is pre-filled from Step 1 |
| ID type: US license or foreign license + license number | Each driver has a US / Foreign toggle, then the issuing state or country, then the license number |
| "+" to add vehicles, VIN only | Step 4. Up to 6 vehicles |
| Check the VIN and show what vehicle it is | Live lookup with the free NHTSA database. Shows year, make, model and trim as they type |
| Full coverage (comp/collision) checkbox next to the VIN | Per-vehicle checkbox, with a deductible picker |
| Roadside and rental next to the VIN | Per-vehicle checkboxes |
| PIP and uninsured motorist | Step 5, for the whole policy, plus a choice of liability limits |
| Secure quote form, spam protection, validation | Browser + server validation, honeypot, timing check, rate limit, same-site check, optional Cloudflare Turnstile |
| License numbers stored encrypted, shown only in the dashboard | AES-256-GCM (`app/lib/crypto.php`). Emails and the CSV export show only the last 4 digits |
| Password-protected dashboard to view every quote | `/admin`. List, search, filters, full details, status, notes, CSV export, multiple users |
| Email notification for each request | Sent to `mail.notify_to`. The customer also gets a confirmation email |
| Round 2: full Spanish version, including the quote form | Every page is also at `/es/…`, with an EN/ES switch in the header. Copy is written inline as `L('English', 'Español')`. Requests remember the language: the dashboard flags Spanish customers and their confirmation email goes out in Spanish |
| Round 2: more than car insurance | Services page + quote form for home, business & contractors (GL, workers comp, builders risk, equipment, fidelity bonds), bonds, title & registration (transfers, plates, temp plates, renewals, salvage, out-of-state) and LLC/DBA. All defined once in `app/lib/content.php` |
| Round 2: personal vs commercial auto | Commercial asks for the company name and offers higher limits (up to $1M combined) |
| Round 2: lienholder when full coverage is chosen | Per vehicle: financed/leased yes/no, plus the lienholder name |
| Round 2: "No license, no problem": any ID accepted | Drivers can use a US license, foreign license or other ID (passport, matrícula consular, state ID). Numbers are encrypted |
| Round 2: 4 Austin offices with different hours | Set in `config.php` under `site.offices`. Shown on Home, Locations and the footer with a live "Open / Closed" badge (Austin time), plus SEO data for each office |
| Basic SEO | Titles/descriptions, canonical URLs, Open Graph image, JSON-LD (InsuranceAgency, FAQPage), sitemap.xml, robots.txt, clean URLs |
| Artwork / UI design | Custom SVG illustrations and animations, icon set, share image, favicons |
| Deploy on HostGator | Steps below |

## Deploying to HostGator (cPanel)

1. **PHP version.** In cPanel > *MultiPHP Manager*, set the domain to **PHP 8.1 or newer** (8.2 or 8.3 recommended).
2. **Database (recommended).** In cPanel > *MySQL® Databases*:
   - create a database, e.g. `cpuser_quotes`
   - create a user with a strong password
   - add the user to the database with **ALL PRIVILEGES**

   (SQLite also works with no setup. MySQL is easier to back up from cPanel.)
3. **Sender mailbox.** In cPanel > *Email Accounts*, create e.g. `no-reply@hisdomain.com`. Mail sent from the site's own domain is much less likely to land in spam.
4. **Edit `public_html/app/config.php`:**
   - `site.*`: agency name, legal name, phone, email, address, hours, social links, and `url` (e.g. `https://www.hisdomain.com`)
   - `db.driver` → `mysql`, then the host (`localhost`), database name, user and password from step 2
   - `mail.notify_to`: the inbox that should receive quote requests. `mail.from`: the mailbox from step 3
   - For the most reliable email, set `mail.transport` to `smtp` and fill in `mail.smtp` (host `mail.hisdomain.com`, port 465, `ssl`, the mailbox and its password)
   - Optional: add free Cloudflare Turnstile keys under `security`
5. **Upload.** Zip the *contents* of `public_html/`, including the hidden `.htaccess` files. In cPanel > *File Manager* > `public_html`, upload the zip and extract it. Make sure `public_html/storage` is writable (755 is fine on HostGator).
6. **SSL.** In cPanel > *SSL/TLS Status*, run AutoSSL. Once `https://` works, uncomment the two "Force HTTPS" lines in `public_html/.htaccess`.
7. **Create the dashboard login.** Visit `https://hisdomain.com/admin/`. The first visit opens a setup page that:
   - checks PHP, storage, database, email and HTTPS
   - asks you to create the first account

   Give the client his own account under *Account → Team access*.
8. **Test end to end.**
   - Send a quote from the site with a real VIN.
   - Confirm it appears in `/admin` and that the notification email arrives.
   - Send a contact message.
9. **Back up `storage/app.key`.** It is created on the first visit. License numbers cannot be decrypted without it. Alternatively, paste its contents into `app_key` in `config.php`.
10. Submit `https://hisdomain.com/sitemap.xml` in Google Search Console.

## Branding

- **Logo:** the EZ Way logo is in `public_html/assets/img/`, set under `site` in `config.php`:
  - `logo.png`: the full logo with a transparent background, used on light backgrounds
  - `logo-white.png`: the footer and dashboard sidebar
  - `logo-mark.png`: the star icon, used in the quote form and to build `favicon.png` and `apple-touch-icon.png`
- **Colors:** the CSS variables at the top of `assets/css/site.css`:
  - `--brand-*` (the logo's royal blue, #2B3990)
  - `--cyan-*` (the logo's light cyan)
  - `--amber-*` (accent)
  - `--navy-*` (dark)

  The dashboard has its own copy at the top of `assets/css/admin.css`.
- **Share image & icon:** open `tools/og-image.html` and `tools/apple-touch-icon.html`. Change the name and colors, then screenshot them at 1200×630 and 180×180 into `assets/img/og-image.png` and `assets/img/apple-touch-icon.png`.
- **Copy:** page text is in the page files, written as `L('English', 'Español')` so both languages sit side by side. Services (and the quote-form questions for each) and auto coverage descriptions are in `app/lib/content.php`. Quote-form options (liability levels, deductibles, relationships, ID types) are at the top of `app/lib/quote.php`. Text used by the scripts is in `app/partials/footer.php` (`$js`).
- **Business details:** phone, email, offices and hours, years in business and number of insurance companies are all in `config.php` under `site`. Add the Texas agency license number to `site.license` to show it in the footer.

## Vercel preview

The repo also deploys to Vercel as a **client preview**. Vercel uses `vercel.json`, which runs PHP with the community `vercel-php` runtime, and `api/index.php`, which maps URLs to `public_html`.

Vercel has no permanent disk. The SQLite database, sessions and encryption key live in `/tmp`, so saved quotes and dashboard logins can reset at any time. Everything else works as on HostGator:

- pages and animations
- the quote form, VIN lookup and ZIP autofill

Use HostGator for the real site.

To redeploy from this folder: `vercel deploy --prod`. For a stable encryption key on the preview, set an `APP_KEY` environment variable in Vercel; any random 32+ character string works.

## Preview locally

```bash
cd website
php -S localhost:8080 -t public_html dev-router.php
# then open http://localhost:8080  (dashboard: http://localhost:8080/admin/)
```

## Notes for maintenance

- Requests are always saved to the dashboard before any email is sent, so a mail problem never loses a lead. Mail errors are logged to `storage/mail.log`, PHP errors to `storage/error.log`.
- VIN lookups use NHTSA's free vPIC service (no key). Results are cached in the database. If NHTSA is down, customers can still continue; the vehicle shows as "Not verified" in the dashboard.
- ZIP → city/state uses the free zippopotam.us service. If it's unavailable, customers simply type the city and state themselves.
- Spam limits are set in `config.php` (`max_quotes_per_hour`, `max_messages_per_hour`, per visitor). Visitor IPs are stored only as a keyed hash.
- `privacy.php` is a starting template. Have it reviewed against the agency's state requirements before launch.
