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

    'timezone' => 'America/New_York',

    // ---------------------------------------------------------------------
    // Agency details (used across every page, in emails and for SEO)
    // ---------------------------------------------------------------------
    'site' => [
        'name'        => 'EZ Way Insurance',
        'legal_name'  => 'EZ Way Insurance',             // CHANGE ME to the registered business name
        'tagline'     => 'Auto insurance, made EZ',
        'description' => 'Get a fast, no-obligation auto insurance quote. Add every driver and vehicle in one simple form and a licensed agent will get back to you with coverage options that fit.',
        // Full public URL with no trailing slash, e.g. https://www.youragency.com
        // Leave empty to detect automatically.
        'url'         => '',
        'phone'       => '(555) 010-2030',               // CHANGE ME
        'email'       => 'hello@youragency.com',         // CHANGE ME
        'address'     => [
            'street' => '123 Main Street, Suite 200',    // CHANGE ME
            'city'   => 'Houston',
            'state'  => 'TX',
            'zip'    => '77002',
        ],
        'hours' => [
            ['Mon – Fri', '9:00 AM – 6:00 PM'],
            ['Saturday',  '10:00 AM – 2:00 PM'],
            ['Sunday',    'Closed'],
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
        'notify_to'   => 'hello@youragency.com',         // CHANGE ME
        // Sender address. Use an address on the site's own domain
        // (create it in cPanel > Email Accounts) so mail isn't flagged as spam.
        'from'        => 'no-reply@youragency.com',      // CHANGE ME
        'from_name'   => 'Your Agency Website',
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
        'max_quotes_per_hour'  => 5,       // per visitor
        'max_messages_per_hour'=> 5,       // per visitor
    ],

    // Encryption key for license numbers. Leave empty and a key is generated
    // automatically in /storage/app.key. BACK THAT FILE UP: without it, saved
    // license numbers cannot be decrypted.
    'app_key' => getenv('APP_KEY') ?: '',
];
