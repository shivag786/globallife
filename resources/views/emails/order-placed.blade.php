@php $money = fn ($n) => 'Rs. '.number_format((float) $n, 2); @endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f5;font-family:Arial,Helvetica,sans-serif;color:#1f2d27;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f5;padding:24px 0;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6ebe8;">
                <tr>
                    <td style="background:#1b3c2c;padding:24px 32px;color:#ffffff;">
                        <span style="font-size:20px;font-weight:bold;letter-spacing:.3px;">Global Life</span>
                        <p style="margin:6px 0 0;color:#a7c3b4;font-size:13px;">Order confirmation</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 32px 8px;">
                        <p style="margin:0 0 4px;font-size:16px;">Hi {{ $order->customer_name }},</p>
                        <p style="margin:0;color:#4a5a52;font-size:14px;line-height:1.6;">
                            Thank you for your order! We've received it and will keep you posted as it ships.
                        </p>
                        <p style="margin:16px 0 0;font-size:14px;">
                            <strong>Order number:</strong> {{ $order->order_number }}<br>
                            <strong>Payment:</strong> {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Paid online' }}
                        </p>
                    </td>
                </tr>

                @if ($plainPassword)
                    <tr>
                        <td style="padding:8px 32px;">
                            <table role="presentation" width="100%" style="background:#f0f7f3;border:1px solid #cfe6da;border-radius:8px;">
                                <tr><td style="padding:16px 18px;">
                                    <p style="margin:0 0 6px;font-weight:bold;color:#1b3c2c;font-size:14px;">Your account is ready</p>
                                    <p style="margin:0;font-size:13px;color:#4a5a52;line-height:1.6;">
                                        Email: <strong>{{ $order->customer_email }}</strong><br>
                                        Temporary password: <strong>{{ $plainPassword }}</strong>
                                    </p>
                                    <p style="margin:8px 0 0;font-size:12px;color:#6b7b73;">Please change your password after logging in.</p>
                                </td></tr>
                            </table>
                        </td>
                    </tr>
                @endif

                <tr>
                    <td style="padding:16px 32px 8px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;">
                            <tr style="background:#f4f6f5;">
                                <th align="left" style="padding:10px 12px;color:#6b7b73;font-weight:normal;">Item</th>
                                <th align="center" style="padding:10px 12px;color:#6b7b73;font-weight:normal;">Qty</th>
                                <th align="right" style="padding:10px 12px;color:#6b7b73;font-weight:normal;">Total</th>
                            </tr>
                            @foreach ($order->items as $item)
                                <tr style="border-bottom:1px solid #eef2f0;">
                                    <td style="padding:12px;">{{ $item->product_name }}</td>
                                    <td align="center" style="padding:12px;">{{ $item->quantity }}</td>
                                    <td align="right" style="padding:12px;">{{ $money($item->line_total) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" align="right" style="padding:8px 12px;color:#6b7b73;">Subtotal</td>
                                <td align="right" style="padding:8px 12px;">{{ $money($order->subtotal) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" align="right" style="padding:4px 12px;color:#6b7b73;">Shipping</td>
                                <td align="right" style="padding:4px 12px;">{{ $order->shipping > 0 ? $money($order->shipping) : 'Free' }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" align="right" style="padding:10px 12px;font-weight:bold;border-top:2px solid #1b3c2c;">Total</td>
                                <td align="right" style="padding:10px 12px;font-weight:bold;border-top:2px solid #1b3c2c;">{{ $money($order->total) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:8px 32px 24px;">
                        <p style="margin:0 0 4px;font-weight:bold;font-size:13px;color:#1b3c2c;">Delivery address</p>
                        <p style="margin:0;font-size:13px;color:#4a5a52;line-height:1.6;">
                            {{ $order->customer_name }}, {{ $order->customer_phone }}<br>
                            {{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}
                        </p>
                        <p style="margin:20px 0 0;">
                            <a href="{{ url('/') }}" style="display:inline-block;background:#245a3f;color:#ffffff;text-decoration:none;padding:11px 22px;border-radius:999px;font-size:14px;">Track your order</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#0d2118;padding:18px 32px;color:#8fae9d;font-size:12px;">
                        © {{ date('Y') }} Global Life. Thank you for shopping with us.
                    </td>
                </tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
