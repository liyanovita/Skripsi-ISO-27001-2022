<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Audit Session Assignment - AuditGuard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #0b2545;
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #0284c7;
        }
        .logo-img {
            height: 42px;
            width: 42px;
            object-fit: contain;
            border-radius: 10px;
            background-color: #ffffff;
            padding: 2px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            vertical-align: middle;
        }
        .header h1 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
            display: inline-block;
            vertical-align: middle;
        }
        .header p {
            color: #38bdf8;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 6px 0 0 0;
        }
        .content {
            padding: 40px 30px;
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .session-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin: 24px 0;
        }
        .session-table {
            width: 100%;
            border-collapse: collapse;
        }
        .session-table td {
            padding: 8px 0;
            font-size: 14px;
            vertical-align: top;
        }
        .label {
            font-weight: 700;
            color: #475569;
            width: 160px;
        }
        .value {
            color: #0f172a;
            font-weight: 600;
        }
        .btn-wrapper {
            text-align: center;
            margin: 32px 0 24px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #0284c7 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            letter-spacing: 0.5px;
        }
        .salutation {
            margin-top: 32px;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
            color: #475569;
            font-size: 14px;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #94a3b8;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                <tr>
                    <td style="vertical-align: middle; padding-right: 10px;">
                        @if(isset($message) && file_exists(public_path('images/logo.jpg')))
                            <img src="{{ $message->embed(public_path('images/logo.jpg')) }}" alt="AuditGuard Logo" class="logo-img">
                        @else
                            <img src="{{ asset('images/logo.jpg') }}" alt="AuditGuard Logo" class="logo-img">
                        @endif
                    </td>
                    <td style="vertical-align: middle;">
                        <h1>AuditGuard</h1>
                    </td>
                </tr>
            </table>
            <p>AI-Assisted ISO/IEC 27001:2022 Compliance Platform</p>
        </div>
        <div class="content">
            <div class="greeting">Hello {{ $user->name ?? 'Auditor' }},</div>
            <p>You have been assigned to a new audit session. Please review the details below:</p>

            <div class="session-card">
                <table class="session-table">
                    <tr>
                        <td class="label">Session Name:</td>
                        <td class="value">{{ $session->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Assigned By:</td>
                        <td class="value">{{ $assignedBy->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Deadline:</td>
                        <td class="value" style="color: #0284c7;">
                            {{ isset($deadline) ? $deadline : ($session->deadline ? $session->deadline->format('d M Y') : '-') }}
                        </td>
                    </tr>
                </table>
            </div>

            <div class="btn-wrapper">
                <a href="{{ $url }}" class="btn" target="_blank">Open Audit Session</a>
            </div>

            <p style="margin-top: 24px;">Thank you for using AuditGuard!</p>

            <div class="salutation">
                <p style="margin: 0 0 4px 0;">Regards,</p>
                <p style="margin: 0; font-weight: 700; color: #0f172a;">Audit Team</p>
            </div>
        </div>
        <div class="footer">
            <p>This email was sent automatically by the AuditGuard platform.</p>
            <p>&copy; {{ date('Y') }} AuditGuard Enterprise. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
