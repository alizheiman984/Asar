<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم الدفع بنجاح</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f0f8ff; }
        h1 { color: green; }
        p { font-size: 18px; }
        .container { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); display: inline-block; }
        a { display: inline-block; margin-top: 20px; text-decoration: none; color: #fff; background: #007bff; padding: 10px 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 تم الدفع بنجاح!</h1>
        <p>شكراً لتبرعك بقيمة: <strong>{{ $payment->amount }} USD</strong></p>
        <p>رقم الدفع: {{ $payment->stripe_payment_intent ?? $payment->id }}</p>
        {{-- <a href="/">العودة إلى الصفحة الرئيسية</a> --}}
    </div>
</body>
</html>
