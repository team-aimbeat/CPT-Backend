<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        body { font-family: Helvetica, sans-serif; color: #333; line-height: 1.5; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .header { text-align: center; margin-bottom: 20px; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .details-table td, .details-table th { padding: 10px; border: 1px solid #eee; text-align: left; }
        .total { font-weight: bold; background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h2>GYM SUBSCRIPTION INVOICE</h2>
            <p>Date: {{ $date }}</p>
        </div>

        @php
            $customerName = $user->display_name ?? $user->name ?? $user->email ?? 'Customer';
            $paymentId = $payment->transaction_id ?? $payment->razorpay_payment_id ?? $payment->id;
            $packageName = optional($package)->name ?? ($subscription->package_data['name'] ?? 'Subscription Package');
            $packageDuration = optional($package)->duration ?? ($subscription->package_data['duration'] ?? null);
            $packageDurationUnit = optional($package)->duration_unit ?? ($subscription->package_data['duration_unit'] ?? null);
            $duration = trim($packageDuration . ' ' . $packageDurationUnit);
        @endphp

        <table class="details-table">
            <tr>
                <td><strong>Customer:</strong> {{ $customerName }}</td>
                <td><strong>Payment ID:</strong> {{ $paymentId }}</td>
            </tr>
            <tr>
                <td><strong>Package:</strong> {{ $packageName }}</td>
                <td><strong>Duration:</strong> {{ $duration }}</td>
            </tr>
        </table>

        <table class="details-table">
            <thead>
                <tr class="total">
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Subscription Fee for {{ $packageName }} ({{ $subscription->subscription_start_date }} to {{ $subscription->subscription_end_date }})</td>
                    <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr class="total">
                    <td style="text-align: right;">Total Paid</td>
                    <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
