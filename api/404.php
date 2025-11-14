<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/bootstrap.php';

if (function_exists('abort')) {
    abort(404, 'Not found');
}

$uri = $uri = $_SERVER['REQUEST_URI'] ?? '/';

if (!headers_sent()) {
    header("HTTP/1.1 404 Not found");

    http_response_code(404);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Found "<?= $uri ?>"</title>
</head>
<body>
    <h2>Not Found "<?= $uri ?>"</h2>

    <h5>Go to <a href="/">home</a></h5>
</body>
</html>
