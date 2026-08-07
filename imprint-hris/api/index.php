<?php

/**
 * Vercel serverless entrypoint.
 *
 * Vercel's filesystem is read-only except for /tmp, so before Laravel boots we
 * point every path it writes to (compiled views, bootstrap cache, framework
 * scratch dirs) at /tmp and create them. /tmp is per-instance and not durable —
 * it is fine for regenerable caches, not for user uploads.
 */

$tmp = '/tmp';

foreach ([
    "$tmp/storage/framework/views",
    "$tmp/storage/framework/cache/data",
    "$tmp/storage/framework/sessions",
    "$tmp/storage/logs",
    "$tmp/storage/app/public",
    "$tmp/bootstrap/cache",
] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Laravel reads these before the container is built.
putenv("VIEW_COMPILED_PATH=$tmp/storage/framework/views");
$_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = "$tmp/storage/framework/views";

putenv('APP_SERVICES_CACHE=' . "$tmp/bootstrap/cache/services.php");
putenv('APP_PACKAGES_CACHE=' . "$tmp/bootstrap/cache/packages.php");
putenv('APP_CONFIG_CACHE=' . "$tmp/bootstrap/cache/config.php");
putenv('APP_ROUTES_CACHE=' . "$tmp/bootstrap/cache/routes-v7.php");
putenv('APP_EVENTS_CACHE=' . "$tmp/bootstrap/cache/events.php");

/**
 * Aiven requires TLS, and PDO's MYSQL_ATTR_SSL_CA option takes a file path —
 * but Vercel env vars only hold text. Materialise the pasted certificate into
 * /tmp and point the option at it.
 */
if ($ca = getenv('AIVEN_CA_CERT')) {
    $caPath = "$tmp/aiven-ca.pem";
    if (! file_exists($caPath)) {
        file_put_contents($caPath, $ca);
    }
    putenv("MYSQL_ATTR_SSL_CA=$caPath");
    $_ENV['MYSQL_ATTR_SSL_CA'] = $_SERVER['MYSQL_ATTR_SSL_CA'] = $caPath;
}

require __DIR__ . '/../public/index.php';
