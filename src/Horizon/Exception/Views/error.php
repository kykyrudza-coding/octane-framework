<?php

declare(strict_types=1);

use Horizon\Arch\Application;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

/** @var FlattenException $error */
/** @var string $displayTitle */
/** @var string $displayClass */
/** @var string $message */
/** @var bool $debug */
$status = $error->getStatusCode();
$statusText = $error->getStatusText();
$file = $error->getFile();
$line = $error->getLine();
$trace = $error->getTrace();
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
$requestUri = $_SERVER['REQUEST_URI'] ?? 'console';

// Read code snippet helper
$readSnippet = static function (string $filePath, int $lineNum): ?array {
    if (is_file($filePath) && is_readable($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        if ($lines !== false) {
            $start = max(1, $lineNum - 8);
            $end = min(count($lines), $lineNum + 8);
            $slices = [];
            for ($n = $start; $n <= $end; $n++) {
                if (array_key_exists($n - 1, $lines)) {
                    $slices[$n] = $lines[$n - 1];
                }
            }

            return [
                'lines' => $slices,
                'target' => $lineNum,
                'file' => $filePath,
            ];
        }
    }

    return null;
};

$mainSnippet = $readSnippet($file, $line);

$frameSnippets = [];
foreach ($trace as $i => $frame) {
    if (isset($frame['file'], $frame['line'])) {
        $frameSnippets[$i] = $readSnippet($frame['file'], (int) $frame['line']);
    }
}

$escape = static fn (mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$shortFile = static function (string $path): string {
    $parts = explode('/', str_replace('\\', '/', $path));

    return implode('/', array_slice($parts, -4));
};
$shortClass = static function (string $fqn): string {
    $parts = explode('\\', $fqn);

    return end($parts);
};
$frameCall = static function (array $frame): string {
    $call = trim(($frame['class'] ?? '').($frame['type'] ?? '').($frame['function'] ?? ''));

    return $call !== '' ? $call : '{closure}';
};
$isAppFrame = static function (array $frame): bool {
    $file = $frame['file'] ?? '';

    return str_contains($file, 'app') || str_contains($file, 'routes') || str_contains($file, 'bootstrap');
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($displayTitle) ?> — Octane</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:              #f5f7fb;
            --surface:         #ffffff;
            --surface-hover:   #f8fafc;
            --surface-active:  #eef2f7;

            --border:          #d8dee9;
            --border-soft:     #e9edf3;

            --text:            #111827;
            --text-inverse:    #ffffff;
            --muted:           #6b7280;
            --muted-dark:      #9ca3af;

            --red:             #ef4444;
            --red-hover:       #dc2626;
            --red-glow:        rgba(239, 68, 68, 0.12);
            --red-border:      rgba(239, 68, 68, 0.25);

            --amber:           #d97706;

            --blue:            #2563eb;
            --blue-glow:       rgba(37, 99, 235, 0.12);
            --blue-border:     rgba(37, 99, 235, 0.25);

            --green:           #10b981;

            --code-bg:         #ffffff;
            --code-text:       #1f2937;

            --glass:           rgba(255, 255, 255, 0.9);
            --soft-fill:       rgba(248, 250, 252, 0.9);
            --tab-bg:          #f8fafc;

            --shadow-sm:       0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-md:       0 8px 24px rgba(0, 0, 0, 0.06);
            --shadow-lg:       0 12px 34px rgba(0, 0, 0, 0.08);

            --radius:          12px;
            --scrollbar:       #cbd5e1;
        }

        html {
            height: 100%;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            font-size: 13.5px;
            line-height: 1.5;
            height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.015'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 10;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--glass);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 1.5rem;
            z-index: 5;
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-icon {
            background: var(--red-glow);
            color: var(--red);
            border: 1px solid var(--red-border);
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border-radius: 6px;
            font-size: 11px;
            box-shadow: 0 0 10px var(--red-glow);
        }

        .logo-text {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
            font-size: 13px;
            color: var(--text);
            letter-spacing: -0.01em;
        }

        .req-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            background: var(--surface-hover);
            padding: 0.3rem 0.75rem;
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .req-method {
            color: var(--green);
            font-weight: 600;
        }
        .req-method.post { color: var(--blue); }
        .req-method.delete { color: var(--red); }
        .req-method.put, .req-method.patch { color: var(--amber); }

        .req-uri {
            color: var(--muted);
        }

        .main-container {
            flex: 1;
            min-height: 0;
            display: grid;
            grid-template-columns: 380px 1fr;
            z-index: 2;
            overflow: hidden;
        }

        /* ─── LEFT PANEL (STACK TRACE) ─────────────────────── */
        .sidebar {
            border-right: 1px solid var(--border);
            background: var(--surface);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 0.9rem 1.25rem;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted-dark);
            border-bottom: 1px solid var(--border);
            font-weight: 700;
        }

        .trace-list {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .trace-item {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-soft);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            position: relative;
        }

        .trace-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: transparent;
            transition: background 0.2s;
        }

        .trace-item:hover {
            background: var(--surface-hover);
        }

        .trace-item.app-frame {
            background: var(--blue-glow);
        }
        .trace-item.app-frame::before {
            background: var(--blue);
        }

        .trace-item.active {
            background: var(--surface-active);
        }
        .trace-item.active::before {
            background: var(--red);
        }

        .trace-item-title {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--text);
            word-break: break-all;
            line-height: 1.4;
        }

        .trace-item.active .trace-item-title {
            color: var(--text);
        }

        .trace-item-title .fn {
            color: var(--blue);
        }
        .trace-item-title .cls {
            color: var(--muted);
        }

        .trace-item-file {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--muted-dark);
            word-break: break-all;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .trace-item-file .line-num {
            color: var(--amber);
        }

        /* ─── MAIN CONTENT ─────────────────────────────────── */
        .content {
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: var(--bg);
            scrollbar-width: thin;
        }

        .hero-section {
            padding: 2.5rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(180deg, var(--surface) 0%, var(--bg) 100%);
            position: relative;
        }

        .exception-class {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 0.75rem;
            letter-spacing: -0.01em;
            background: var(--surface-hover);
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            border: 1px solid var(--border);
        }

        .exception-message {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.35;
            color: var(--red);
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }

        .exception-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            font-size: 12.5px;
            color: var(--muted);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .meta-item span {
            color: var(--text);
            font-weight: 500;
            font-family: 'JetBrains Mono', monospace;
        }

        .action-bar {
            margin-top: 1.75rem;
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover {
            border-color: var(--muted);
            background: var(--surface-hover);
        }

        .btn-primary {
            background: var(--red);
            border-color: var(--red);
            color: var(--text-inverse);
        }

        .btn-primary:hover {
            background: var(--red-hover);
            border-color: var(--red-hover);
        }

        .workspace-section {
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
        }

        /* ─── CODE PREVIEW ─────────────────────────────────── */
        .code-box {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--code-bg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .code-box-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1.25rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .code-box-header .copy-path {
            cursor: pointer;
            transition: color 0.15s;
        }
        .code-box-header .copy-path:hover {
            color: var(--text);
        }

        .code-lines {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            line-height: 1.7;
            padding: 1.25rem 0;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .code-row {
            display: grid;
            grid-template-columns: 60px 1fr;
            color: var(--code-text);
            position: relative;
        }

        .code-row.active {
            background: var(--red-glow);
        }

        .code-row.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--red);
            box-shadow: 0 0 10px var(--red);
        }

        .code-num {
            color: var(--muted-dark);
            text-align: right;
            padding-right: 1.5rem;
            user-select: none;
            border-right: 1px solid var(--border-soft);
        }

        .code-row.active .code-num {
            color: var(--red);
            border-right-color: var(--red-border);
        }

        .code-text {
            padding-left: 1.5rem;
            white-space: pre;
        }

        /* ─── TABS CONTEXT ─────────────────────────────────── */
        .tabs-container {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .tabs-header {
            display: flex;
            background: var(--tab-bg);
            border-bottom: 1px solid var(--border);
            padding: 0.5rem 0.5rem 0 0.5rem;
            gap: 0.25rem;
        }

        .tab-btn {
            background: none;
            border: 1px solid transparent;
            border-bottom: none;
            color: var(--muted);
            padding: 0.6rem 1.5rem;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            transition: all 0.2s;
            font-family: inherit;
        }

        .tab-btn:hover {
            color: var(--text);
            background: var(--surface-hover);
        }

        .tab-btn.active {
            background: var(--surface);
            color: var(--text);
            border-color: var(--border);
            box-shadow: var(--shadow-sm);
        }

        .tab-content {
            display: none;
            padding: 1.5rem;
            max-height: 500px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .tab-content.active {
            display: block;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px;
        }

        .data-table td {
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid var(--border-soft);
            vertical-align: top;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table td.key {
            color: var(--blue);
            width: 250px;
            font-weight: 500;
        }

        .data-table td.val {
            color: var(--text);
            word-break: break-all;
        }

        .empty-state {
            color: var(--muted-dark);
            font-style: italic;
            font-size: 12.5px;
            padding: 1rem;
            text-align: center;
        }

        /* SCROLLBAR STYLES */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--scrollbar);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--muted-dark);
        }
    </style>
</head>
<body>

<header>
    <div class="logo-wrap">
        <span class="logo-icon">✕</span>
        <span class="logo-text">Octane Exception Inspector</span>
    </div>
    <div class="req-info">
        <span class="req-method <?= strtolower($requestMethod) ?>"><?= $escape($requestMethod) ?></span>
        <span class="req-uri"><?= $escape($requestUri) ?></span>
    </div>
</header>

<div class="main-container">
    <!-- SIDEBAR (STACK TRACE) -->
    <div class="sidebar">
        <div class="sidebar-header">Stack Trace (<?= count($trace) ?> frames)</div>
        <div class="trace-list">
            <!-- Main active frame -->
            <div id="item-main" class="trace-item app-frame active" onclick="selectFrame('main')">
                <span class="trace-item-title"><?= $escape($shortClass($displayClass)) ?></span>
                <span class="trace-item-file"><?= $escape($shortFile($file)) ?>:<span class="line-num"><?= $escape($line) ?></span></span>
            </div>

            <!-- Trace frames -->
            <?php foreach ($trace as $i => $frame) {
                $app = $isAppFrame($frame);
                $call = $frameCall($frame);
                $locFile = $frame['file'] ?? null;
                $locLine = $frame['line'] ?? null;
                ?>
                <div id="item-frame-<?= $i ?>" class="trace-item <?= $app ? 'app-frame' : '' ?>" onclick="selectFrame('frame-<?= $i ?>')">
                    <span class="trace-item-title">
                        <?php if (isset($frame['class'])) { ?>
                            <span class="cls"><?= $escape($shortClass($frame['class'])) ?><?= $escape($frame['type']) ?></span><?php } ?><span class="fn"><?= $escape($frame['function']) ?></span>
                    </span>
                    <span class="trace-item-file">
                        <?php if ($locFile) { ?>
                            <?= $escape($shortFile($locFile)) ?>:<span class="line-num"><?= $escape($locLine) ?></span>
                        <?php } else { ?>
                            [internal call]
                        <?php } ?>
                    </span>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="content">
        <div class="hero-section">
            <div class="exception-class"><?= $escape($displayClass) ?></div>
            <h1 class="exception-message"><?= $escape($message) ?></h1>
            <div class="exception-meta">
                <div class="meta-item">Status: <span><?= $escape($status) ?> <?= $escape($statusText) ?></span></div>
                <div class="meta-item">File: <span><?= $escape($file) ?></span></div>
                <div class="meta-item">Line: <span><?= $escape($line) ?></span></div>
            </div>
            <div class="action-bar">
                <button class="btn btn-primary" onclick="copyError()">Copy Message</button>
            </div>
        </div>

        <div class="workspace-section">
            <!-- CODE PREVIEWS CONTAINER (Managed via JS) -->
            <div class="code-previews-container">
                <!-- Main Error Code Box -->
                <?php if ($mainSnippet) { ?>
                    <div id="code-main" class="code-box">
                        <div class="code-box-header">
                            <span class="copy-path" onclick="copyText('<?= $escape(str_replace('\\', '\\\\', $file)) ?>:<?= $line ?>')"><?= $escape($file) ?></span>
                            <span style="color: var(--amber)">Line <?= $escape($line) ?></span>
                        </div>
                        <div class="code-lines">
                            <?php foreach ($mainSnippet['lines'] as $n => $src) { ?>
                                <div class="code-row <?= $n === $line ? 'active' : '' ?>">
                                    <span class="code-num"><?= $escape($n) ?></span>
                                    <span class="code-text"><?= $escape($src) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <!-- Trace Frames Code Boxes -->
                <?php foreach ($frameSnippets as $i => $snippet) {
                    if ($snippet) { ?>
                        <div id="code-frame-<?= $i ?>" class="code-box" style="display: none;">
                            <div class="code-box-header">
                                <span class="copy-path" onclick="copyText('<?= $escape(str_replace('\\', '\\\\', $snippet['file'])) ?>:<?= $snippet['target'] ?>')"><?= $escape($snippet['file']) ?></span>
                                <span style="color: var(--amber)">Line <?= $escape($snippet['target']) ?></span>
                            </div>
                            <div class="code-lines">
                                <?php foreach ($snippet['lines'] as $n => $src) { ?>
                                    <div class="code-row <?= $n === $snippet['target'] ? 'active' : '' ?>">
                                        <span class="code-num"><?= $escape($n) ?></span>
                                        <span class="code-text"><?= $escape($src) ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div id="code-frame-<?= $i ?>" class="code-box" style="display: none;">
                            <div class="code-box-header">
                                <span>No source code available</span>
                            </div>
                            <div class="empty-state">
                                This frame represents an internal PHP function or a class method without accessible source files.
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>

            <!-- TABS CONTEXT -->
            <div class="tabs-container">
                <div class="tabs-header">
                    <button id="btn-request" class="tab-btn active" onclick="switchTab('request')">Request</button>
                    <button id="btn-server" class="tab-btn" onclick="switchTab('server')">Server</button>
                    <button id="btn-app" class="tab-btn" onclick="switchTab('app')">App State</button>
                </div>

                <!-- REQUEST TAB -->
                <div id="tab-request" class="tab-content active">
                    <h3 style="margin-bottom: 0.75rem; font-size: 13px; color: var(--muted)">Query Parameters</h3>
                    <?php if (! empty($_GET)) { ?>
                        <table class="data-table">
                            <?php foreach ($_GET as $k => $v) { ?>
                                <tr>
                                    <td class="key"><?= $escape($k) ?></td>
                                    <td class="val"><?= is_array($v) ? json_encode($v) : $escape($v) ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <div class="empty-state">No query parameters present.</div>
                    <?php } ?>

                    <h3 style="margin: 2rem 0 0.75rem 0; font-size: 13px; color: var(--muted)">Request Body</h3>
                    <?php if (! empty($_POST)) { ?>
                        <table class="data-table">
                            <?php foreach ($_POST as $k => $v) { ?>
                                <tr>
                                    <td class="key"><?= $escape($k) ?></td>
                                    <td class="val"><?= is_array($v) ? json_encode($v) : $escape($v) ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <div class="empty-state">No request body parameters present.</div>
                    <?php } ?>

                    <h3 style="margin: 2rem 0 0.75rem 0; font-size: 13px; color: var(--muted)">Headers</h3>
                    <?php
                    $headers = function_exists('getallheaders') ? getallheaders() : [];
if (empty($headers)) {
    foreach ($_SERVER as $name => $value) {
        if (str_starts_with($name, 'HTTP_')) {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
}
if (! empty($headers)) { ?>
                        <table class="data-table">
                            <?php foreach ($headers as $k => $v) { ?>
                                <tr>
                                    <td class="key"><?= $escape($k) ?></td>
                                    <td class="val"><?= $escape($v) ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <div class="empty-state">No headers available.</div>
                    <?php } ?>
                </div>

                <!-- SERVER TAB -->
                <div id="tab-server" class="tab-content">
                    <table class="data-table">
                        <?php foreach ($_SERVER as $k => $v) { ?>
                            <tr>
                                <td class="key"><?= $escape($k) ?></td>
                                <td class="val"><?= is_array($v) ? json_encode($v) : $escape($v) ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>

                <!-- APP STATE TAB -->
                <div id="tab-app" class="tab-content">
                    <table class="data-table">
                        <tr>
                            <td class="key">Octane Version</td>
                            <td class="val"><?= $escape(Application::version()) ?></td>
                        </tr>
                        <tr>
                            <td class="key">PHP Version</td>
                            <td class="val"><?= $escape(PHP_VERSION) ?></td>
                        </tr>
                        <tr>
                            <td class="key">OS Sapi</td>
                            <td class="val"><?= $escape(PHP_SAPI) ?></td>
                        </tr>
                        <tr>
                            <td class="key">Base Path</td>
                            <td class="val"><?= $escape(app()->basePath()) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let activeFrameId = 'main';

    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        document.getElementById('btn-' + tabId).classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    function selectFrame(frameId) {
        document.querySelectorAll('.trace-item').forEach(item => item.classList.remove('active'));
        document.getElementById('item-' + frameId).classList.add('active');

        document.querySelectorAll('.code-box').forEach(box => box.style.display = 'none');
        const targetBox = document.getElementById('code-' + frameId);
        if (targetBox) {
            targetBox.style.display = 'block';
        }
        activeFrameId = frameId;
    }

    function copyText(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('File path copied to clipboard!');
        });
    }

    function copyError() {
        const errorText = `<?= $escape($displayClass) ?>: <?= $escape($message) ?>\n\nFile: <?= $escape($file) ?>:<?= $escape($line) ?>\n\nStack Trace:\n<?php foreach ($trace as $i => $frame) {
            $call = $frameCall($frame);
            $locFile = $frame['file'] ?? '[internal]';
            $locLine = $frame['line'] ?? '?';
            echo "  #$i $locFile($locLine): $call\\n";
        } ?>`;
        navigator.clipboard.writeText(errorText).then(() => {
            const btn = document.querySelector('.btn-primary');
            const orig = btn.innerText;
            btn.innerText = 'Copied!';
            setTimeout(() => btn.innerText = orig, 1500);
        });
    }
</script>
</body>
</html>
