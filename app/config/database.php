<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'recipe_platform');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {

    static $pdo = null;

    if ($pdo === null) {

        $dsn = "mysql:host=" . DB_HOST .
               ";dbname=" . DB_NAME .
               ";charset=" . DB_CHARSET;

        try {

            $pdo = new PDO($dsn, DB_USER, DB_PASS);

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    return $pdo;
}
// Base path — set this to '' if running at server root (e.g. via vhost)
// Set to '/Web_Tech_Project/recipe-sharing-platform/public' if in XAMPP htdocs subfolder
define('APP_BASE', '/Web_Tech_Project/recipe-sharing-platform/public');

function url(string $path): string {
    return APP_BASE . $path;
}
