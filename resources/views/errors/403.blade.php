<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>غير مصرح — منصة خطوة</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,700,800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .error-wrap { text-align: center; padding: 2rem 1rem; }
        .error-code {
            font-size: clamp(5rem, 20vw, 9rem);
            font-weight: 800; line-height: 1;
            background: linear-gradient(135deg, #dc3545, #a71d2a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .error-icon { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: .3; }
        h2 { font-weight: 700; color: #333; }
        p  { color: #888; max-width: 420px; margin: .5rem auto 2rem; }
        .btn-home {
            background: linear-gradient(135deg, #2C5AA0, #1e4085);
            color: #fff; border: none;
            padding: .75rem 2.5rem; border-radius: 50px;
            font-size: 1rem; font-weight: 600;
            font-family: inherit; text-decoration: none;
            display: inline-block; transition: opacity .2s;
        }
        .btn-home:hover { opacity: .88; color: #fff; }
        .btn-back {
            color: #666; text-decoration: none;
            font-size: .9rem; margin-top: 1rem; display: block;
        }
    </style>
</head>
<body>
    <div class="error-wrap">
        <span class="error-icon">🔒</span>
        <div class="error-code">403</div>
        <h2>غير مصرح لك بهذا الإجراء</h2>
        <p>ليس لديك صلاحية للوصول إلى هذه الصفحة أو تنفيذ هذا الإجراء.</p>
        <a href="{{ url('/') }}" class="btn-home">العودة للصفحة الرئيسية</a>
        <a href="javascript:history.back()" class="btn-back">← العودة للصفحة السابقة</a>
    </div>
</body>
</html>
