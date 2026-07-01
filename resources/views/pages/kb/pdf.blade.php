<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            padding: 38px;
            color: #1f2937;
            line-height: 1.6;
        }

        .eyebrow {
            color: #4f46e5;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        h1 {
            color: #111827;
            font-size: 24px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 14px;
            margin: 0 0 14px;
        }

        .description {
            font-size: 12px;
            color: #4b5563;
            margin-bottom: 18px;
        }

        .meta {
            width: 100%;
            margin: 18px 0 24px;
            border-collapse: collapse;
            font-size: 11px;
            color: #4b5563;
        }

        .meta td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .content-body {
            font-size: 12px;
            color: #1f2937;
            margin-top: 24px;
        }

        .content-body h1, .content-body h2, .content-body h3, .content-body h4 {
            color: #111827;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .content-body p {
            margin-bottom: 12px;
        }

        .content-body ul, .content-body ol {
            margin-left: 24px;
            margin-bottom: 12px;
        }

        .content-body li {
            margin-bottom: 6px;
        }

        .content-body code {
            background: #f1f5f9;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 11px;
        }

        .content-body pre {
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            font-size: 11px;
            white-space: pre-wrap;
            border: 1px solid #e2e8f0;
            color: #111827;
            margin-bottom: 16px;
            overflow-x: auto;
        }

        .content-body table {
            width: 100%;
            margin: 18px 0;
            border-collapse: collapse;
            font-size: 11px;
            color: #1f2937;
        }

        .content-body table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: left;
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
        }

        .content-body table td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
        }

        .content-body blockquote {
            border-left: 4px solid #4f46e5;
            padding-left: 14px;
            color: #4b5563;
            font-style: italic;
            margin: 14px 0;
        }

        .footer {
            margin-top: 42px;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.jpg');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        }
    @endphp
    <div style="margin-bottom: 20px; border-bottom: 2px solid #008B9B; padding-bottom: 12px;">
        <table style="width: 100%; border: none; margin-bottom: 0;">
            <tr>
                <td style="width: 50px; border: none; padding: 0; vertical-align: middle;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="height: 45px; width: 45px; border-radius: 8px;">
                    @endif
                </td>
                <td style="border: none; padding: 0 0 0 10px; vertical-align: middle; text-align: left;">
                    <div style="font-size: 20px; font-weight: bold; line-height: 1.1;">
                        <span style="color: #0B2545;">Audit</span><span style="color: #008B9B;">Guard</span>
                    </div>
                    <div style="font-size: 7px; font-weight: 900; color: #64748b; letter-spacing: 2px; margin-top: 2px; text-transform: uppercase;">
                        ASSESS &bull; ANALYZE &bull; ASSURE
                    </div>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: middle; color: #475569;">
                    <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #4f46e5;">ISO 27001:2022 Knowledge Base</div>
                    <div style="font-size: 8px; margin-top: 3px; color: #64748b;">PDF Export &nbsp;|&nbsp; {{ $generatedDate }}</div>
                </td>
            </tr>
        </table>
    </div>
    <h1>{{ $item->title }}</h1>
    <p class="description">{{ $item->description ?? 'ISO 27001:2022 implementation resource' }}</p>

    <table class="meta">
        <tr>
            <td><strong>Category</strong><br>{{ $item->category === 'sop' ? 'SOP' : ucfirst((string) $item->category) }}</td>
            <td><strong>Format</strong><br>PDF Export</td>
            <td><strong>Generated</strong><br>{{ $generatedDate }}</td>
        </tr>
    </table>

    <div class="content-body">
        @if(trim((string) $item->content) !== '')
            @if($item->isHtml())
                {!! $item->content !!}
            @else
                {!! Str::markdown(e($item->content)) !!}
            @endif
        @else
            <div style="text-align: center; padding: 30px; background: #f9fafb; border: 1px dashed #e5e7eb; border-radius: 8px; color: #4b5563; font-size: 11px;">
                <p style="font-weight: bold; margin-bottom: 5px;">Attachment-Only Document</p>
                <p>This resource does not contain inline article text. Please access the application to download the original attached document ({{ $item->attachment_name ?: 'source file' }}).</p>
            </div>
        @endif
    </div>

    <div class="footer">AuditGuard &copy; {{ date('Y') }} | ISO 27001:2022 Knowledge Base Resource</div>
</body>
</html>
