<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>{{ $county->name }} Megye</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f7f6; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $county->name }} Megye Települései</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Irányítószám</th>
                <th>Település Neve</th>
            </tr>
        </thead>
        <tbody>
            @forelse($places as $place)
                <tr>
                    <td>{{ $place->id }}</td>
                    <td>{{ $place->postal_code }}</td>
                    <td>{{ $place->name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Nincs megjeleníthető település.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>