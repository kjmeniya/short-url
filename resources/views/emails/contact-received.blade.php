<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
        }

        .info-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #0d6efd;
            border-radius: 4px;
        }

        .info-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 120px;
        }

        .value {
            color: #212529;
        }

        .message-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 style="margin: 0;">📧 New Contact Message</h1>
    </div>

    <div class="content">
        <p>Hello Admin,</p>
        <p>You have received a new contact message from your website.</p>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #0d6efd;">Contact Information</h3>
            <div class="info-row">
                <span class="label">Name:</span>
                <span class="value">{{ $contact->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value"><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></span>
            </div>
            <div class="info-row">
                <span class="label">Received:</span>
                <span class="value">{{ formatUserDateTime($contact->created_at) }}</span>
            </div>
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #0d6efd;">Subject</h3>
            <p style="margin: 0; font-size: 16px; font-weight: 500;">{{ $contact->subject }}</p>
        </div>

        <div class="message-box">
            <h3 style="margin-top: 0; color: #0d6efd;">Message</h3>
            <p style="white-space: pre-wrap; margin: 0;">{{ $contact->message }}</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('admin.contacts.show', $contact->id) }}" class="button">
                View & Reply to Message
            </a>
        </div>

        <div class="footer">
            <p>This is an automated notification from {{ config('app.name') }}</p>
            <p>You can manage all contact messages in your <a href="{{ route('admin.contacts.index') }}">admin panel</a></p>
        </div>
    </div>
</body>

</html>