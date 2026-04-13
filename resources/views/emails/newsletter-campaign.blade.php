<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaignSubject }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; padding: 30px 20px; }
        .wrapper { max-width: 620px; margin: 0 auto; }
        .header { background: #093E66; color: #fff; padding: 24px 28px; border-radius: 12px 12px 0 0; }
        .header h2 { font-size: 20px; font-weight: 700; }
        .header p { font-size: 13px; opacity: 0.85; margin-top: 4px; }
        .body { background: #fff; padding: 28px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px; }
        .body p { font-size: 15px; color: #1f2937; line-height: 1.7; white-space: pre-wrap; }
        .footer { margin-top: 22px; text-align: center; font-size: 12px; color: #9ca3af; }
        @media (max-width: 640px) {
            body { padding: 16px 10px; }
            .header, .body { padding: 20px 18px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h2>{{ $campaignSubject }}</h2>
            <p>{{ config('app.name') }}</p>
        </div>
        <div class="body">
            <p>{{ $campaignBody }}</p>
        </div>
        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }} · Recibiste este email porque estás suscripto a nuestro newsletter.
        </div>
    </div>
</body>
</html>
