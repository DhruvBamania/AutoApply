<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Inquiry Received</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px 0; }
        .wrapper { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%); padding: 24px 28px; color: #ffffff; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: rgba(255,255,255,0.2); color: #ffffff; margin-top: 8px; }
        .content { padding: 28px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .info-table td { padding: 8px 12px; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        .info-table td.label { font-weight: 600; color: #64748b; width: 140px; }
        .message-box { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #4f46e5; border-radius: 8px; padding: 16px 20px; margin: 20px 0; line-height: 1.6; font-size: 15px; color: #334155; white-space: pre-wrap; }
        .footer { padding: 20px 28px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>📩 New Inbound Contact Submission</h1>
            <span class="badge">{{ $msg->ticket_id }} &bull; {{ $msg->category_label }}</span>
        </div>
        <div class="content">
            <p style="font-size: 15px; margin-top: 0;">A new user inquiry has been submitted through the public Contact Us form.</p>
            
            <table class="info-table">
                <tr>
                    <td class="label">Ticket Reference:</td>
                    <td><strong style="color: #4338ca;">{{ $msg->ticket_id }}</strong></td>
                </tr>
                <tr>
                    <td class="label">From Name:</td>
                    <td><strong>{{ $msg->name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Email Address:</td>
                    <td><a href="mailto:{{ $msg->email }}" style="color: #4f46e5;">{{ $msg->email }}</a></td>
                </tr>
                @if($msg->phone)
                <tr>
                    <td class="label">Phone:</td>
                    <td>{{ $msg->phone }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Category:</td>
                    <td>{{ $msg->category_label }}</td>
                </tr>
                <tr>
                    <td class="label">Subject:</td>
                    <td><strong>{{ $msg->subject }}</strong></td>
                </tr>
                <tr>
                    <td class="label">IP Address:</td>
                    <td><span style="font-family: monospace; font-size: 12px; color: #64748b;">{{ $msg->ip_address ?? 'N/A' }}</span></td>
                </tr>
                <tr>
                    <td class="label">Submitted At:</td>
                    <td>{{ $msg->created_at->format('M d, Y - h:i A T') }}</td>
                </tr>
            </table>

            <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 16px 0 8px 0;">Message Content:</h3>
            <div class="message-box">{{ $msg->message }}</div>

            <div style="margin-top: 24px; text-align: center;">
                <a href="mailto:{{ $msg->email }}?subject={{ rawurlencode('Re: [' . $msg->ticket_id . '] ' . $msg->subject) }}" style="display: inline-block; background: #4f46e5; color: #ffffff; text-decoration: none; padding: 10px 22px; border-radius: 8px; font-size: 14px; font-weight: 600;">Reply to {{ $msg->name }}</a>
            </div>
        </div>
        <div class="footer">
            AutoApply &amp; AI Looping Autonomous Recruitment Platform &bull; Production Notification System
        </div>
    </div>
</body>
</html>
