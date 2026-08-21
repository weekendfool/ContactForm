<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'お問い合わせフォーム')</title>
        {{-- Node/npmが未導入の環境のためVite経由のTailwindは使わず、素のCSSで最小限の見た目を整える --}}
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Hiragino Kaku Gothic ProN, Meiryo, sans-serif;
                background-color: #f5f5f4;
                color: #1c1917;
                margin: 0;
                padding: 2rem 1rem;
            }
            .container {
                max-width: 640px;
                margin: 0 auto;
                background-color: #fff;
                border-radius: 0.5rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                padding: 2rem;
            }
            h1 {
                font-size: 1.5rem;
                margin-top: 0;
            }
            label {
                display: block;
                font-weight: 600;
                margin-top: 1rem;
                margin-bottom: 0.25rem;
            }
            input[type="text"],
            input[type="email"],
            input[type="password"],
            textarea,
            select {
                width: 100%;
                box-sizing: border-box;
                padding: 0.5rem;
                border: 1px solid #d6d3d1;
                border-radius: 0.25rem;
                font-size: 1rem;
            }
            textarea {
                min-height: 8rem;
                resize: vertical;
            }
            .error {
                color: #dc2626;
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }
            .actions {
                margin-top: 1.5rem;
                display: flex;
                gap: 0.75rem;
            }
            button,
            .btn {
                display: inline-block;
                padding: 0.6rem 1.25rem;
                border-radius: 0.25rem;
                border: 1px solid #292524;
                background-color: #292524;
                color: #fff;
                font-size: 1rem;
                cursor: pointer;
                text-decoration: none;
            }
            .btn-secondary {
                background-color: #fff;
                color: #292524;
            }
            .flash {
                background-color: #ecfdf5;
                border: 1px solid #10b981;
                color: #065f46;
                padding: 0.75rem 1rem;
                border-radius: 0.25rem;
                margin-bottom: 1rem;
            }
            dl {
                margin: 0;
            }
            dt {
                font-weight: 600;
                color: #57534e;
                margin-top: 1rem;
            }
            dd {
                margin: 0.25rem 0 0;
                white-space: pre-wrap;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                text-align: left;
                padding: 0.5rem;
                border-bottom: 1px solid #e7e5e4;
            }
            th {
                color: #57534e;
                font-size: 0.875rem;
            }
            a {
                color: #292524;
            }
            .top-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .top-bar h1 {
                margin: 0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
