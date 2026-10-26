<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry Confirmation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px 0; }
        .wrapper { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #312e81 0%, #4338ca 100%); padding: 28px; color: #ffffff; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0 0; font-size: 14px; color: #c7d2fe; }
        .content { padding: 32px 28px; }
        .ticket-card { background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 16px 20px; text-align: center; margin: 20px 0; }
        .ticket-number { font-size: 22px; font-weight: 800; color: #3730a3; letter-spacing: 0.5px; }
        .message-summary { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 20px; font-size: 14px; color: #475569; margin: 20px 0; }
        .faq-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px 20px; margin-top: 24px; font-size: 13.5px; color: #166534; line-height: 1.5; }
        .footer { padding: 20px 28px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>We've Received Your Message! 👋</h1>
            <p>Thank you for reaching out to AutoApply &amp; AI Looping</p>
        </div>
        <div class="content">
            <p style="font-size: 15px; margin-top: 0;">Hi <strong>{{ $msg->name }}</strong>,</p>
            <p style="font-size: 14.5px; line-height: 1.6; color: #334155;">
                Thank you for contacting us regarding <strong>{{ $msg->subject }}</strong>. Our support and client success teams have received your inquiry and are reviewing it.
            </p>

            <div class="ticket-card">
                <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #6366f1; font-weight: 700; margin-bottom: 4px;">Your Support Reference Ticket</div>
                <div class="ticket-number">{{ $msg->ticket_id }}</div>
                <div style="font-size: 12.5px; color: #4338ca; margin-top: 4px;">Department: {{ $msg->category_label }}</div>
            </div>

            <div class="message-summary">
                <strong style="display: block; margin-bottom: 6px; color: #1e293b;">Copy of Your Inquiry:</strong>
                <div style="white-space: pre-wrap; font-style: italic;">"{{ $msg->message }}"</div>
            </div>

            <div class="faq-box">
                <strong style="display: block; margin-bottom: 4px;">⏱️ Typical Response Times:</strong>
                Our team responds to all inquiries within <strong>24 business hours</strong> (Monday through Friday, 9:00 AM – 6:00 PM EST). For high-priority enterprise recruitment or security matters, our emergency desks operate 24/7.
            </div>

            <p style="font-size: 14px; color: #64748b; margin-top: 24px;">
                Warm regards,<br>
                <strong>The AutoApply &amp; AI Looping Team</strong><br>
                <a href="{{ config('app.url', 'http://127.0.0.1:8000') }}" style="color: #4f46e5; font-size: 13px;">{{ config('app.name', 'AutoApply') }}</a>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} AutoApply &amp; AI Looping. All rights reserved. &bull; <a href="{{ route('terms') }}" style="color: #64748b;">Terms</a> &bull; <a href="{{ route('privacy') }}" style="color: #64748b;">Privacy</a>
        </div>
    </div>
</body>
</html>
