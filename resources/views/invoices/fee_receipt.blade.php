<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Fee Receipt</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .header h1 {
            color: #d32f2f;
            /* Red branding color */
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            color: #777;
            font-size: 12px;
        }

        .invoice-info {
            display: flex;
            /* dompdf has partial flex support, usually standard floats/tables are safer */
            justify-content: space-between;
            margin-bottom: 30px;
        }

        /* Using table for layout since CSS flex/grid support in dompdf is limited */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            vertical-align: top;
        }

        .client-info {
            width: 60%;
        }

        .receipt-info {
            width: 40%;
            text-align: right;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .details-table th,
        .details-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .details-table th {
            background-color: #f9f9f9;
            font-weight: bold;
            color: #555;
        }

        .details-table td.amount {
            text-align: right;
        }

        .total-section {
            text-align: right;
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            background-color: #e0f2f1;
            color: #00695c;
        }

        .badge.failed {
            background-color: #ffebee;
            color: #c62828;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>RKKF Institute</h1>
            <p>Fee Payment Receipt</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="client-info">
                    <strong>Student Details:</strong><br>
                    {{ $student->firstname }} {{ $student->lastname }}<br>
                    GR No: {{ $student->student_id }}<br>
                    Branch: {{ $student->branch->name ?? 'N/A' }}<br>
                    Belt: {{ $student->belt->name ?? 'N/A' }}
                </td>
                <td class="receipt-info">
                    <strong>Receipt #:</strong> {{ $receipt_no }}<br>
                    <strong>Date:</strong> {{ $date }}<br>
                    <strong>Mode:</strong> {{ $payment_mode }}
                </td>
            </tr>
        </table>

        <table class="details-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Ref</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Tuition Fees<br>
                        <small style="color: #666;">Periods: {{ $months_display }}</small>
                    </td>
                    <td>{{ $transaction_ref }}</td>
                    <td class="amount">₹{{ number_format($amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            Total Paid: ₹{{ number_format($amount, 2) }}
        </div>

        <div class="footer">
            <p>This is a computer-generated receipt and does not require a signature.</p>
            <p>Thank you for your payment!</p>
        </div>
    </div>
</body>

</html>