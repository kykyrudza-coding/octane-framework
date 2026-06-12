<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 — Error</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: Inter, system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .error {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .code {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -0.03em;
        }

        .divider {
            width: 1px;
            height: 28px;
            background: var(--border);
        }

        .message {
            color: var(--muted);
            font-size: 0.95rem;
            font-weight: 400;
        }

        @media (max-width: 640px) {
            .error {
                flex-direction: column;
                gap: 0.75rem;
            }

            .divider {
                width: 32px;
                height: 1px;
            }

            .code {
                font-size: 1.25rem;
            }

            .message {
                text-align: center;
            }
        }
    </style>
</head>
<body>

<main class="error">
    <span class="code">503</span>
    <div class="divider"></div>
    <p class="message">
        The server is currently unavailable. Please try again later.
    </p>
</main>

</body>
</html>
