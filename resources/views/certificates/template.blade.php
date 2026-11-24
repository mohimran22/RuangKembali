<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    html, body { margin: 0; padding: 0; width: {{ $w }}pt; height: {{ $h }}pt; overflow: hidden; }
    body { font-family: DejaVu Sans, sans-serif; }
    .bg { position: absolute; top: 0; left: 0; width: {{ $w }}pt; height: {{ $h }}pt; }
    .name {
        position: absolute; left: 0; width: {{ $w }}pt; text-align: center;
        top: {{ $top }}pt; font-size: {{ $size }}pt; font-weight: bold; color: {{ $color }};
    }
    .code { position: absolute; left: 18pt; bottom: 10pt; font-size: 6pt; color: #888; }
</style>
</head>
<body>
    <img class="bg" src="{{ $path }}">
    <div class="name">{{ $name }}</div>
    <div class="code">Verifikasi: {{ $verify }} &middot; {{ $code }}</div>
</body>
</html>