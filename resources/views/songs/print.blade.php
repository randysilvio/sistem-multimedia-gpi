<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak: {{ $song->title }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Georgia&display=swap');
        body { font-family: 'Georgia', serif; color: #000; padding: 40px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-size: 24px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .book { font-size: 16px; color: #555; margin-top: 5px; }
        .verse { margin-bottom: 20px; font-size: 16px; white-space: pre-wrap;}
        .verse-num { font-weight: bold; float: left; margin-right: 15px; font-size: 18px; }
        .chorus { margin-left: 30px; border-left: 3px solid #ccc; padding-left: 15px; font-style: italic; font-weight: bold; color: #333; margin-bottom: 20px; white-space: pre-wrap;}
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="padding: 10px 20px; margin-bottom: 20px; cursor: pointer; font-weight: bold;">Cetak Dokumen</button>
    
    <div class="header">
        <div class="title">{{ $song->title }}</div>
        <div class="book">Buku: {{ $song->book }} No. {{ $song->number }}</div>
    </div>

    @foreach($song->verses as $index => $verse)
        <div class="verse">
            <span class="verse-num">{{ $index + 1 }}.</span>
            <div style="overflow: hidden;">{{ $verse }}</div>
        </div>
        
        @if($song->chorus)
            <div class="chorus">Reff:<br>{{ $song->chorus }}</div>
        @endif
    @endforeach

</body>
</html>