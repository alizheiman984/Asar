<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رمز التحقق الخاص بك</title>
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
            direction: rtl;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .otp-code {
            font-size: 24px;
            font-weight: bold;
            color: #1d72b8;
            text-align: center;
            margin: 20px 0;
            letter-spacing: 5px;
        }
        .footer {
            font-size: 14px;
            color: #777;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>مرحباً!</h2>
    <p>لقد طلبت رمز تحقق لتسجيل الدخول أو إنشاء حساب جديد.</p>

    <div class="otp-code">
        {{ $otp }}
    </div>

    <p>يرجى استخدام هذا الرمز خلال <strong>٥ دقائق</strong> قبل انتهاء صلاحيته.</p>

    <div class="footer">
        إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة.
    </div>
</div>

</body>
</html>
