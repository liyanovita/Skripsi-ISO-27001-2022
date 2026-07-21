<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('New Task Assignment') }}</title>
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
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .lead-text {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .task-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }
        .btn-container {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #008b9b;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 30px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px -1px rgba(0, 89, 99, 0.2);
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
            <p>{{ __('ISMS Compliance Platform') }}</p>
        </div>
        <div class="content">
            <h2 class="greeting">{{ __('Hello') }}, {{ $user->name }}!</h2>
            <p class="lead-text">
                {{ __('You have been assigned as the Personnel In Charge (PIC) for a compliance control remediation task. Please review the details below:') }}
            </p>

            <div class="task-card">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #475569; width: 130px; vertical-align: top;">{{ __('Control') }}:</td>
                        <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600; vertical-align: top;">
                            <span class="badge">{{ $result->standard->code ?? '-' }}</span>
                            <div style="margin-top: 5px; font-weight: 600; color: #1e293b;">{{ $result->standard->title ?? '-' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #475569; vertical-align: top;">{{ __('Assessment') }}:</td>
                        <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600; vertical-align: top;">{{ $result->session->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; font-weight: 700; color: #475569; vertical-align: top;">{{ __('Due Date') }}:</td>
                        <td style="padding: 6px 0; font-size: 13px; color: #e11d48; font-weight: 700; vertical-align: top;">
                            {{ $result->treatment_due_date ? $result->treatment_due_date->format('d M Y') : __('Not yet determined') }}
                        </td>
                    </tr>
                </table>
            </div>

            <p class="lead-text" style="margin-bottom: 10px;">
                {{ __('Please promptly update the status and follow up on the corrective action in the Compliance Center.') }}
            </p>

            <div class="btn-container">
                <a href="{{ route('workspace.index', ['session_id' => $result->session_id, 'focus' => $result->id]) }}" class="btn">
                    {{ __('Open Compliance Center') }}
                </a>
            </div>
        </div>
        <div class="footer">
            <p>{{ __('This email was sent automatically by the AuditGuard platform.') }}</p>
            <p>&copy; {{ date('Y') }} AuditGuard. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
