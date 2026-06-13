<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ config('app.name', 'Zayka Dining') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #d97706;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #d97706;
        }
        .company-details {
            text-align: right;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table td {
            vertical-align: top;
            padding: 5px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .items-table th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-box {
            float: right;
            width: 300px;
            background-color: #f9fafb;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        .total-box table {
            width: 100%;
        }
        .total-box td {
            padding: 5px 0;
        }
        .total-box td.label {
            font-weight: bold;
        }
        .total-box td.value {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #d97706;
        }
        .footer {
            margin-top: 100px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header">
    <table>
        <tr>
            <td>
                <span class="title">INVOICE</span><br>
                <span>Invoice #: INV-{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</span><br>
                <span>Date: {{ now()->format('F d, Y') }}</span>
            </td>
            <td class="company-details">
                <strong>{{ $restaurant->name }}</strong><br>
                {{ $restaurant->address }}<br>
                Phone: {{ $restaurant->phone }}<br>
                Email: {{ $restaurant->email }}
            </td>
        </tr>
    </table>
</div>

<table class="details-table">
    <tr>
        <td style="width: 50%;">
            <strong>Billed To:</strong><br>
            {{ $user->name }}<br>
            Email: {{ $user->email }}
        </td>
        <td style="width: 50%; text-align: right;">
            <strong>Booking Reservation Details:</strong><br>
            Date: {{ $booking->booking_date->format('F d, Y') }}<br>
            Time: {{ \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') }}<br>
            Guests: {{ $booking->guest_count }} People<br>
            Table: {{ $table ? $table->name : 'Auto-Assigned' }}
        </td>
    </tr>
</table>

<table class="items-table">
    <thead>
        <tr>
            <th>Description</th>
            <th>Booking Option</th>
            <th style="text-align: right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                Table Reservation fee for {{ $restaurant->name }}<br>
                <small style="color: #6b7280;">Capacity: {{ $table ? $table->capacity : $booking->guest_count }} Guests</small>
            </td>
            <td>
                {{ ucfirst($booking->payment_type) }} Payment
            </td>
            <td style="text-align: right; font-weight: bold;">
                ₹{{ number_format($booking->payment_amount, 2) }}
            </td>
        </tr>
    </tbody>
</table>

<div class="total-box">
    <table>
        <tr>
            <td class="label">Payment Method:</td>
            <td style="text-align: right;">Stripe Card</td>
        </tr>
        <tr>
            <td class="label">Status:</td>
            <td style="text-align: right; font-weight: bold; color: green;">Paid</td>
        </tr>
        <tr>
            <td colspan="2" style="border-top: 1px solid #e5e7eb; margin: 10px 0;"></td>
        </tr>
        <tr>
            <td class="label">Total Paid:</td>
            <td class="value">₹{{ number_format($booking->payment_amount, 2) }}</td>
        </tr>
    </table>
</div>

<div class="footer">
    <p>Thank you for dining with us! If you need to modify or cancel your booking, please contact the restaurant directly.</p>
    <p>&copy; {{ now()->year }} {{ $restaurant->name }}. All rights reserved.</p>
</div>

</body>
</html>
