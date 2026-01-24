<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Exam Result Certificate - {{ $certificate_no }}</title>
    <style>
        @page {
            margin: 30px;
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.4;
        }

        .outer-border {
            border: 4px solid #C9A227;
            padding: 8px;
        }

        .inner-border {
            border: 2px solid #C9A227;
            padding: 30px 40px;
            min-height: 750px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-text {
            font-size: 42px;
            font-weight: bold;
            color: #C9A227;
            letter-spacing: 6px;
            margin-bottom: 8px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #1a1a2e;
            letter-spacing: 3px;
            text-transform: uppercase;
            border-bottom: 2px solid #C9A227;
            display: inline-block;
            padding-bottom: 5px;
        }

        .intro-text {
            text-align: center;
            font-size: 13px;
            color: #666;
            margin: 20px 0 30px 0;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .details-table tr {
            border-bottom: 1px solid #eee;
        }

        .details-table td {
            padding: 12px 10px;
            vertical-align: middle;
        }

        .details-table .label {
            width: 35%;
            text-align: right;
            padding-right: 20px;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .details-table .value {
            width: 65%;
            text-align: left;
            font-size: 15px;
            color: #222;
        }

        .status-pass {
            color: #16a34a;
            font-weight: bold;
            font-size: 16px;
        }

        .status-fail {
            color: #dc2626;
            font-weight: bold;
            font-size: 16px;
        }

        .belt-section {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }

        .belt-label {
            font-size: 11px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .belt-transition {
            font-size: 18px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .belt-arrow {
            color: #C9A227;
            font-size: 20px;
            margin: 0 15px;
        }

        .remarks {
            text-align: center;
            font-style: italic;
            color: #555;
            font-size: 15px;
            margin: 25px 0;
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding: 10px 20px;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 180px;
            margin: 0 auto 8px auto;
        }

        .signature-name {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="outer-border">
        <div class="inner-border">
            <!-- Header -->
            <div class="header">
                <div class="logo-text">RKKF</div>
                <div class="title">Certificate of Achievement</div>
            </div>

            <!-- Intro -->
            <p class="intro-text">
                This is to certify that the student has successfully completed the examination.
            </p>

            <!-- Details Table -->
            <table class="details-table">
                <tr>
                    <td class="label">Certificate No:</td>
                    <td class="value">{{ $certificate_no }}</td>
                </tr>
                <tr>
                    <td class="label">Exam Name:</td>
                    <td class="value">{{ $exam_name }}</td>
                </tr>
                <tr>
                    <td class="label">Date:</td>
                    <td class="value">{{ \Carbon\Carbon::parse($exam_date)->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Location:</td>
                    <td class="value">{{ $location }}</td>
                </tr>
                <tr>
                    <td class="label">Result:</td>
                    <td class="value">
                        <span class="{{ $is_passed ? 'status-pass' : 'status-fail' }}">
                            {{ strtoupper($status) }}
                        </span>
                    </td>
                </tr>
            </table>

            <!-- Belt Transition -->
            <div class="belt-section">
                <div class="belt-label">Rank Promotion</div>
                <div class="belt-transition">
                    {{ $belt_transition['from'] }}
                    <span class="belt-arrow">→</span>
                    {{ $belt_transition['to'] }}
                </div>
            </div>

            <!-- Remarks -->
            <div class="remarks">
                "{{ $remarks }}"
            </div>

            <!-- Signatures -->
            <div class="signature-section">
                <table class="signature-table">
                    <tr>
                        <td>
                            <div class="signature-line"></div>
                            <div class="signature-name">Examiner Signature</div>
                        </td>
                        <td>
                            <div class="signature-line"></div>
                            <div class="signature-name">Director Signature</div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Footer -->
            <div class="footer">
                Certificate No: {{ $certificate_no }}<br>
                Generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t h:i A') }}
            </div>
        </div>
    </div>
</body>

</html>