<?php

$uri = '/' . trim('admin/dashboard', '/');
$requestUri = '/' . trim('/admin/dashboard', '/');
$pattern = preg_replace('/\{(\w+)}/', '(?P<$1>[^/]+)', $uri);
$pattern = '#^'.$pattern.'$#';
echo (preg_match($pattern, $requestUri, $matches) ? 'MATCH' : 'NO MATCH');
