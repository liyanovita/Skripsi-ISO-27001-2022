<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Alert</title>
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
            border-bottom: 4px solid #008b9b;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .header p {
            color: #008b9b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 5px 0 0 0;
        }
        .content {
            padding: 40px 30px;
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
        }
        .lead-text {
            margin-bottom: 25px;
            white-space: pre-line;
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
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>AuditGuard</h1>
            <p>ISMS Compliance Platform</p>
        </div>
        <div class="content">
            <div class="lead-text">
                {!! nl2br(e($bodyText)) !!}
            </div>
        </div>
        <div class="footer">
            <p>This email was sent automatically by the AuditGuard platform.</p>
            <p>&copy; {{ date('Y') }} AuditGuard. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
