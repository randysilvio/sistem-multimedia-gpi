<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Lirik: {{ $song->title }}</title>
    <style>
        /* Menggunakan font Serif agar terlihat klasik seperti buku nyanyian gereja */
        @import url('https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap');
        
        body { 
            font-family: 'Merriweather', Georgia, serif; 
            color: #1a202c; 
            padding: 40px 60px; 
            line-height: 1.6; 
            background: #f8fafc;
        }
        
        .paper-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 50px 60px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-radius: 8px;
        }

        .header { 
            text-align: center; 
            margin-bottom: 40px; 
            border-bottom: 2px solid #cbd5e0; 
            padding-bottom: 20px; 
        }
        .book-badge {
            display: inline-block;
            font-family: sans-serif;
            background: #2b6cb0;
            color: #fff;
            padding: 4px 12px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }
        .title { 
            font-size: 28px; 
            font-weight: 700; 
            margin: 0; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            color: #2d3748;
        }

        .lyrics-section {
            font-size: 16px;
        }

        .verse-block {
            display: flex;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .verse-num { 
            font-weight: bold; 
            font-size: 18px; 
            min-width: 40px;
            color: #4a5568;
        }
        
        .verse-text {
            white-space: pre-wrap;
            flex: 1;
        }

        .chorus-block { 
            margin-left: 40px; 
            border-left: 3px solid #2b6cb0; 
            padding-left: 20px; 
            margin-bottom: 25px; 
            page-break-inside: avoid;
            background: #f8fafc;
            padding-top: 10px;
            padding-bottom: 10px;
            border-radius: 0 4px 4px 0;
        }
        
        .chorus-label {
            font-family: sans-serif;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2b6cb0;
            margin-bottom: 5px;
        }

        .chorus-text {
            font-style: italic; 
            font-weight: bold; 
            color: #2d3748; 
            white-space: pre-wrap;
        }

        .btn-print {
            position: fixed; 
            bottom: 30px; 
            right: 30px; 
            z-index: 1000;
            background: #1a202c; 
            color: white; 
            padding: 12px 25px;
            border: none; 
            border-radius: 50px; 
            font-size: 14px; 
            cursor: pointer; 
            font-weight: bold;
            font-family: sans-serif;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: 0.2s;
        }
        .btn-print:hover { background: #2d3748; transform: translateY(-2px); }

        @media print { 
            body { padding: 0; background: #ffffff; } 
            .paper-container { box-shadow: none; padding: 0; max-width: 100%; border-radius: 0;}
            .btn-print { display: none; } 
        }
    </style>
</head>
<body>
    
    <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    
    <div class="paper-container">
        <div class="header">
            <div class="book-badge">{{ $song->book }} {{ $song->number ? '- No. ' . $song->number : '' }}</div>
            <div class="title">{{ $song->title }}</div>
        </div>

        <div class="lyrics-section">
            @foreach($song->verses as $index => $verse)
                
                <div class="verse-block">
                    <div class="verse-num">{{ $index + 1 }}.</div>
                    <div class="verse-text">{{ $verse }}</div>
                </div>
                
                @if($song->chorus)
                    <div class="chorus-block">
                        <div class="chorus-label">Refrein :</div>
                        <div class="chorus-text">{{ $song->chorus }}</div>
                    </div>
                @endif
                
            @endforeach
        </div>
        
        <div style="margin-top: 50px; text-align: center; font-family: sans-serif; font-size: 11px; color: #a0aec0;">
            Dicetak dari Sistem Multimedia GPI Papua - {{ date('d M Y') }}
        </div>
    </div>

</body>
</html>