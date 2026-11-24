<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Sertifikat</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 40px 16px; }
        .box { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 32px; text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .ok { color: #188038; font-weight: bold; font-size: 18px; }
        .name { font-size: 24px; font-weight: bold; margin: 16px 0 4px; }
        .muted { color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="box">
        <div class="ok">✔ Sertifikat Valid</div>
        <div class="name">{{ $name }}</div>
        <div class="muted">telah mengikuti acara</div>
        <div style="font-weight:bold;margin-top:8px">{{ $event->name }}</div>
        <div class="muted">{{ $event->start_at->translatedFormat('d F Y') }}</div>
    </div>
</body>
</html>