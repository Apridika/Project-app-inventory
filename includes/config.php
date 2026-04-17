<?php

define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/ui/');

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}