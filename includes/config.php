<?php

define('BASE_URL', 'http://localhost/ui/');

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}