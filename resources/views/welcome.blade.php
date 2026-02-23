<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postal Database</title>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .county-card {
            background-color: #ffffff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .county-title {
            margin-top: 0;
            font-size: 1.4em;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .place-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .place-item {
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .postal-code {
            font-weight: bold;
            color: #7f8c8d;
            margin-right: 5px;
        }
        .more-places {
            font-size: 0.85em;
            color: #95a5a6;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Hungary Postal Database</h1>

        @foreach($counties as $county)
            <div class="county-card">
                <h2 class="county-title">{{ $county->name }} County</h2>
                
                <ul class="place-list">
                    {{-- Just showing the first 10 places so the page doesn't get massively long --}}
                    @foreach($county->places->take(10) as $place)
                        <li class="place-item">
                            <span class="postal-code">{{ $place->postal_code }}</span> 
                            {{ $place->name }}
                        </li>
                    @endforeach
                </ul>
                
                @if($county->places->count() > 10)
                    <p class="more-places">+ {{ $county->places->count() - 10 }} more places...</p>
                @endif
            </div>
        @endforeach
    </div>

</body>
</html>