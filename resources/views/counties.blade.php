<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Megyék</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; color: #333; padding: 20px; max-width: 1000px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { padding: 8px; width: 100%; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        
        button, .btn-link { padding: 8px 15px; background: #3498db; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px;}
        button:hover, .btn-link:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-success { background: #2ecc71; }
        .btn-success:hover { background: #27ae60; }
        .btn-secondary { background: #95a5a6; }
        .btn-secondary:hover { background: #7f8c8d; }
        
        .initials-list { display: flex; gap: 5px; margin-bottom: 20px; flex-wrap: wrap; }
        .initials-list a { padding: 5px 10px; background: #ecf0f1; text-decoration: none; color: #333; border-radius: 4px; }
        .initials-list a.active { background: #3498db; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .action-flex { display: flex; gap: 5px; }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 400px; position: relative; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .close-btn { position: absolute; right: 20px; top: 15px; font-size: 24px; font-weight: bold; cursor: pointer; color: #aaa; }
        .close-btn:hover { color: #333; }
    </style>
</head>
<body>

    <div class="header">
        @if(session('success'))
            <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                {{ session('success') }}
            </div>
        @endif
        <h1>Magyar Postai Címjegyzék</h1>
        <div>
            @guest
                <form action="{{ route('login') }}" method="POST" style="display: flex; gap: 10px; align-items: center;">
                    @csrf
                    <input type="email" name="email" placeholder="Email" required style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
                    <input type="password" name="password" placeholder="Jelszó" required style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
                    <button type="submit">Bejelentkezés</button>
                </form>
                @error('email') <div style="color: red; font-size: 0.8em; margin-top: 5px;">Helytelen bejelentkezési adatok.</div> @enderror
            @endguest

            @auth
                <span style="font-weight: bold;">Üdvözlünk, {{ Auth::user()->name }}!</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline; margin-left: 10px;">
                    @csrf
                    <button type="submit" class="btn-danger">Kijelentkezés</button>
                </form>
            @endauth
        </div>
    </div>

    <div class="card">
        <form action="{{ route('counties.index') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
            <div class="form-group" style="margin: 0; max-width: 300px;">
                <label>Válassz egy megyét:</label>
                <select name="county_id" onchange="this.form.submit()">
                    <option value="">-- Válassz egy megyét --</option>
                    @foreach($counties as $county)
                        <option value="{{ $county->id }}" {{ $selectedCountyId == $county->id ? 'selected' : '' }}>
                            {{ $county->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($selectedInitial)
                <input type="hidden" name="initial" value="{{ $selectedInitial }}">
            @endif
        </form>
    </div>

    @if($selectedCountyId)
        <div class="card">
            <h3>Szűrés kezdőbetű alapján</h3>
            <div class="initials-list">
                <a href="{{ route('counties.index', ['county_id' => $selectedCountyId]) }}" class="{{ !$selectedInitial ? 'active' : '' }}">Összes</a>
                @foreach($initials as $initial)
                    <a href="{{ route('counties.index', ['county_id' => $selectedCountyId, 'initial' => $initial]) }}" class="{{ $selectedInitial == $initial ? 'active' : '' }}">
                        {{ $initial }}
                    </a>
                @endforeach
            </div>

            <div class="action-bar">
                <h4 style="margin: 0;">Települések listája</h4>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('counties.downloadCsv', ['county' => $selectedCountyId, 'initial' => $selectedInitial]) }}" class="btn-link btn-secondary">CSV Letöltése</a>
                    <a href="{{ route('counties.downloadPdf', ['county' => $selectedCountyId, 'initial' => $selectedInitial]) }}" class="btn-link" style="background-color: #e67e22;">PDF Letöltése</a>
                    
                    @auth
                        <a href="{{ route('counties.emailPdf', ['county' => $selectedCountyId, 'initial' => $selectedInitial]) }}" class="btn-link" style="background-color: #8e44ad;">E-mail Küldése</a>
                        
                        <button type="button" class="btn-success" onclick="openModal()">+ Új település hozzáadása</button>
                    @endauth
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 150px;">Irányítószám</th>
                        <th>Település</th>
                        @auth <th style="width: 200px;">Műveletek</th> @endauth
                    </tr>
                </thead>
                <tbody>
                    @forelse($places as $place)
                        <tr>
                            @guest
                                <td>{{ $place->postal_code }}</td>
                                <td>{{ $place->name }}</td>
                            @endguest
                            
                            @auth
                                <td>
                                    <form id="edit-{{ $place->id }}" action="{{ route('places.update', $place) }}" method="POST">
                                        @csrf @method('PUT')
                                    </form>
                                    <input type="text" name="postal_code" value="{{ $place->postal_code }}" form="edit-{{ $place->id }}" required style="width: 100px; padding: 5px;">
                                </td>
                                <td>
                                    <input type="text" name="name" value="{{ $place->name }}" form="edit-{{ $place->id }}" required style="width: 100%; padding: 5px;">
                                </td>
                                <td class="action-flex">
                                    <button type="submit" form="edit-{{ $place->id }}">Mentés</button>
                                    <form action="{{ route('places.destroy', $place) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger" onclick="return confirm('Biztos törölni szeretnéd ezt a várost?')">Törlés</button>
                                    </form>
                                </td>
                            @endauth
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Nincs város a megadott szűrökkel.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="card">
            <p>Válassz egy megyét a listából</p>
        </div>
    @endif

    @auth
    <div id="addPlaceModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Új település hozzáadása</h3>
            
            <form action="{{ route('places.store') }}" method="POST">
                @csrf
                <input type="hidden" name="county_id" value="{{ $selectedCountyId }}">
                
                <div class="form-group">
                    <label>Irányítószám (ZIP)</label>
                    <input type="text" name="postal_code" required placeholder="pl. 1011">
                </div>
                
                <div class="form-group">
                    <label>Település neve</label>
                    <input type="text" name="name" required placeholder="pl. Budapest">
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="closeModal()" style="margin-right: 5px;">Mégsem</button>
                    <button type="submit" class="btn-success">Hozzáadás</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('addPlaceModal');
        function openModal() { modal.style.display = 'block'; }
        function closeModal() { modal.style.display = 'none'; }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
    @endauth

</body>
</html>