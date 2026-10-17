<?php
/**
 * Site configuration.
 *
 * This is the only file you need to edit when deploying. Everything marked
 * "CHANGE ME" should be filled in with the agency's real details.
 */
defined('APP') or exit;

return [

    // Turn on only while debugging. Never leave on in production.
    'debug' => false,

    'timezone' => 'America/Chicago',   // Austin, TX

    // Spanish version of the website (/es/ pages, ES switch, Spanish quote form
    // and emails). Everything is built; set to true to switch it on.
    'spanish' => false,

    // ---------------------------------------------------------------------
    // Agency details (used across every page, in emails and for SEO).
    // Text with 'en' and 'es' keys is shown in the visitor's language.
    // ---------------------------------------------------------------------
    'site' => [
        'name'        => 'EZ Way Insurance',
        'legal_name'  => 'EZ Way Insurance LLC',
        'tagline'     => [
            'en' => 'Insurance & auto services, made EZ',
            'es' => 'Seguros y servicios de auto, fácil',
        ],
        'description' => [
            'en' => 'Car, home and business insurance from 20+ companies, plus title, plates and registration services. 4 offices in Austin, TX. Se habla español. Any type of ID accepted.',
            'es' => 'Seguros de auto, casa y negocio con más de 20 compañías, además de títulos, placas y registro. 4 oficinas en Austin, TX. Aceptamos cualquier tipo de ID.',
        ],
        // Full public URL with no trailing slash, e.g. https://www.ezwayinsurance.com
        // Leave empty to detect automatically.
        'url'         => '',
        'phone'       => '(512) 679-2682',
        'email'       => 'ezwayuw@gmail.com',
        'license'     => '',          // Texas agency license number; shown in the footer when set
        'licensed_in' => 'Texas',
        'years'       => 10,          // "10+ years"
        'carriers'    => 20,          // "20+ insurance companies"

        // Offices. The first one is the main office (used for SEO and emails).
        // Hours use 24h time; null means closed that day.
        'offices' => [
            [
                'key' => 'cameron', 'name' => 'Cameron Rd',
                'street' => '7901 Cameron Rd, Ste 5D', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78754',
                'hours' => ['weekdays' => ['09:00', '19:30'], 'sat' => ['10:00', '18:00'], 'sun' => ['10:00', '17:00']],
            ],
            [
                'key' => 'cannon', 'name' => 'William Cannon',
                'street' => '2004 E William Cannon Dr, Ste 105', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78744',
                'hours' => ['weekdays' => ['09:00', '19:00'], 'sat' => ['10:00', '18:00'], 'sun' => null],
            ],
            [
                'key' => 'lamar', 'name' => 'North Lamar',
                'street' => '9515 N Lamar Blvd, Ste 112', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78753',
                'hours' => ['weekdays' => ['09:00', '19:00'], 'sat' => ['10:00', '18:00'], 'sun' => null],
            ],
            [
                'key' => 'howard', 'name' => 'Howard Ln',
                'street' => '1200 W Howard Ln, Ste N', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78753',
                'hours' => ['weekdays' => ['09:00', '19:00'], 'sat' => ['10:00', '18:00'], 'sun' => null],
            ],
        ],
        // Logo files inside /assets/img. Leave empty to use a built-in shield and the name.
        'logo'        => 'logo.png',        // full logo for light backgrounds
        'logo_dark'   => 'logo-white.png',  // white version for the footer and dashboard sidebar
        'logo_mark'   => 'logo-mark.png',   // icon only (quote form badge, sign-in page)
        'social' => [
            'facebook'  => '',
            'instagram' => '',
            'google'    => '',   // Google Business Profile link
        ],
    ],

    // ---------------------------------------------------------------------
    // Database
    // 'sqlite' works with zero setup. On HostGator, 'mysql' is recommended:
    // create a database + user in cPanel > MySQL Databases and enter it below.
    // Tables are created automatically on first visit.
    // ---------------------------------------------------------------------
    'db' => [
        'driver' => 'sqlite',            // 'sqlite' or 'mysql'
        'host'   => 'localhost',
        'port'   => 3306,
        'name'   => 'cpaneluser_quotes',
        'user'   => 'cpaneluser_quotes',
        'pass'   => '',
    ],

    // ---------------------------------------------------------------------
    // Email notifications
    // ---------------------------------------------------------------------
    'mail' => [
        // Where new quote requests and contact messages are sent.
        'notify_to'   => 'ezwayuw@gmail.com',
        // Sender address. Use an address on the site's own domain
        // (create it in cPanel > Email Accounts) so mail isn't flagged as spam.
        'from'        => 'no-reply@yourdomain.com',      // CHANGE ME once the domain is known
        'from_name'   => 'EZ Way Insurance',
        // Send the customer a "we got your request" email.
        'confirm_customer' => true,

        // 'mail' uses the server's built-in mail (works on HostGator).
        // 'smtp' sends through a mailbox, which is more reliable.
        'transport'   => 'mail',
        'smtp' => [
            'host'   => 'mail.youragency.com',
            'port'   => 465,
            'secure' => 'ssl',               // 'ssl' (465) or 'tls' (587)
            'user'   => 'no-reply@youragency.com',
            'pass'   => '',
        ],
    ],

    // ---------------------------------------------------------------------
    // Spam protection
    // Honeypot, timing checks and rate limits are always on. For an extra
    // layer, add free Cloudflare Turnstile keys (dash.cloudflare.com > Turnstile).
    // ---------------------------------------------------------------------
    'security' => [
        'turnstile_site_key'   => '',
        'turnstile_secret_key' => '',
        'max_quotes_per_hour'  => 5,        // per visitor
        'max_messages_per_hour'=> 5,       // per visitor
    ],

    // Encryption key for license numbers. Leave empty and a key is generated
    // automatically in /storage/app.key. BACK THAT FILE UP: without it, saved
    // license numbers cannot be decrypted.
    'app_key' => getenv('APP_KEY') ?: '',
];
