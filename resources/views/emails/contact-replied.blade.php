<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Your Message</title>
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
            background: #198754;
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

        .reply-box {
            background: #d1e7dd;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #198754;
            border-radius: 4px;
        }

        .original-message {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            border: 1px solid #dee2e6;
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
        <h1 style="margin: 0;">✉️ Reply to Your Message</h1>
    </div>

    <div class="content">
        <p>Hello {{ $contact->name }},</p>
        <p>Thank you for contacting us. We have reviewed your message and here is our response:</p>

        <div class="reply-box">
            <h3 style="margin-top: 0; color: #198754;">Our Reply</h3>
            <p style="white-space: pre-wrap; margin: 0; font-size: 15px;">{{ $contact->reply_message }}</p>
        </div>

        <div class="original-message">
            <h3 style="margin-top: 0; color: #6c757d;">Your Original Message</h3>
            <div class="info-row">
                <span class="label">Subject:</span>
                <span class="value">{{ $contact->subject }}</span>
            </div>
            <div class="info-row">
                <span class="label">Sent:</span>
                <span class="value">{{ formatUserDateTime($contact->created_at) }}</span>
            </div>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                <p style="white-space: pre-wrap; margin: 0; color: #6c757d;">{{ $contact->message }}</p>
            </div>
        </div>

        <p style="margin-top: 20px;">If you have any further questions, please feel free to reply to this email or contact us again through our website.</p>

        <div class="footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>This email was sent in response to your contact form submission.</p>
            @if(config('mail.from.address'))
            <p>You can reply directly to this email at: <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a></p>
            @endif
        </div>
    </div>
</body>

</html>