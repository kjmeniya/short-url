<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'Email from ' . site_name() }}</title>
    <style>
        body {
            font-family: 'Roboto', Helvetica, sans-serif;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 10px;
        }

        .header {
            padding: 20px;
            text-align: center;
        }

        .header img {
            max-height: 40px;
            width: auto;
        }

        .content {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .content h2{
            margin-bottom: 10px;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #245dac;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .button:hover {
            color: #ffffff;
        }

        .button.primary {
            background: #245dac;
        }

        .button.theme {
            background: #245dac;
        }

        .footer {
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
            border-top: 2px solid #dee2e6;
            margin-top: 20px;
        }

        .footer img {
            max-height: 30px;
            width: auto;
            margin-bottom: 10px;
        }

        a {
            color: #245dac;
            text-decoration: none;
        }

        a:hover {
            color: #1a4a8a;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Common Header -->
        <div class="header">
            <img src="{{ asset('build/images/logo-dark.png') }}" alt="{{ site_name() }}" />
            <!-- <h1 style="margin: 10px 0 0 0; color: #333;">{{ site_name() }}</h1> -->
        </div>

        <!-- Email Content -->
        {!! $content !!}

        <!-- Common Footer -->
        <div class="footer">
            <img src="{{ asset('build/images/logo-dark.png') }}" alt="{{ site_name() }}" />
            <p>&copy; 2025 - {{ date('Y') }} {{ site_name() }}. All rights reserved.</p>
            <p style="font-size: 12px;"><a href="{{ app_url() }}">{{ app_url() }}</a></p>
        </div>
    </div>
</body>

</html>

