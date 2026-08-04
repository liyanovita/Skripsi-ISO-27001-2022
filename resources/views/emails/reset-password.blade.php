<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - AuditGuard</title>
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
        .header h1 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
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
        .btn-wrapper {
            text-align: center;
            margin: 32px 0;
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
        .note {
            background-color: #f1f5f9;
            border-left: 4px solid #0284c7;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            color: #475569;
            margin-top: 24px;
        }
        .subtext {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 30px;
            word-break: break-all;
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
            <h1>🛡️ AuditGuard</h1>
            <p>AI-Assisted ISO/IEC 27001:2022 Compliance Platform</p>
        </div>
        <div class="content">
            <div class="greeting">Hello, {{ $userName }}!</div>
            <p>You are receiving this email because we received a password reset request for your account on the <strong>AuditGuard ISO/IEC 27001:2022</strong> platform.</p>
            <p>Please click the button below to proceed with setting up a new password:</p>
            
            <div class="btn-wrapper">
                <a href="{{ $resetUrl }}" class="btn" target="_blank">Reset Password</a>
            </div>

            <div class="note">
                <strong>💡 Security Notice:</strong> This password reset link will expire in <strong>60 minutes</strong>.
            </div>

            <p style="margin-top: 24px;">If you did not request a password reset, no further action is required and your account password will remain secure.</p>

            <div class="subtext">
                If you are having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
                <a href="{{ $resetUrl }}" style="color: #2563eb;">{{ $resetUrl }}</a>
            </div>
        </div>
        <div class="footer">
            <p>This email was sent automatically by the AuditGuard system.</p>
            <p>&copy; {{ date('Y') }} AuditGuard Enterprise. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
