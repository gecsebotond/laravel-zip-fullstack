<!DOCTYPE html>
<html lang="en">
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
        .form-group input, .form-group select { padding: 8px; width: 100%; max-width: 300px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 8px 15px; background: #3498db; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .initials-list { display: flex; gap: 5px; margin-bottom: 20px; flex-wrap: wrap; }
        .initials-list a { padding: 5px 10px; background: #ecf0f1; text-decoration: none; color: #333; border-radius: 4px; }
        .initials-list a.active { background: #3498db; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .action-flex { display: flex; gap: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Magyar Postai Címjegyzék</h1>
        <div>
            @guest
                <form action="{{ route('login') }}" method="POST" style="display: flex; gap: 10px; align-items: center;">
                    @csrf
                    <input type="email" name="email" placeholder="Email" required style="padding: 6px;">
                    <input type="password" name="password" placeholder="Password" required style="padding: 6px;">
                    <button type="submit">Login</button>
                </form>
                @error('email') <div style="color: red; font-size: 0.8em; margin-top: 5px;">{{ $message }}</div> @enderror
            @endguest

            @auth
                <span style="font-weight: bold;">Welcome, {{ Auth::user()->name }}!</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline; margin-left: 10px;">
                    @csrf
                    <button type="submit" class="btn-danger">Logout</button>
                </form>
            @endauth
        </div>
    </div>

    <div class="card">
        <form action="{{ route('counties.index') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
            <div class="form-group" style="margin: 0;">
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
            <h3>Filter by Initial</h3>
            <div class="initials-list">
                <a href="{{ route('counties.index', ['county_id' => $selectedCountyId]) }}" class="{{ !$selectedInitial ? 'active' : '' }}">All</a>
                @foreach($initials as $initial)
                    <a href="{{ route('counties.index', ['county_id' => $selectedCountyId, 'initial' => $initial]) }}" class="{{ $selectedInitial == $initial ? 'active' : '' }}">
                        {{ $initial }}
                    </a>
                @endforeach
            </div>

            @auth
                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                    <h4>Új település hozzádadása</h4>
                    <form action="{{ route('places.store') }}" method="POST" style="display: flex; gap: 10px; align-items: flex-end;">
                        @csrf
                        <input type="hidden" name="county_id" value="{{ $selectedCountyId }}">
                        <div class="form-group" style="margin: 0;">
                            <label>ZIP</label>
                            <input type="text" name="postal_code" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>Település</label>
                            <input type="text" name="name" required>
                        </div>
                        <button type="submit">Save New Place</button>
                    </form>
                </div>
            @endauth

            <table>
                <thead>
                    <tr>
                        <th>ZIP</th>
                        <th>Település</th>
                        @auth <th>Műveletek</th> @endauth
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
                                    <input type="text" name="postal_code" value="{{ $place->postal_code }}" form="edit-{{ $place->id }}" required style="width: 80px; padding: 5px;">
                                </td>
                                <td>
                                    <input type="text" name="name" value="{{ $place->name }}" form="edit-{{ $place->id }}" required style="width: 100%; padding: 5px;">
                                </td>
                                <td class="action-flex">
                                    <button type="submit" form="edit-{{ $place->id }}">Update</button>
                                    <form action="{{ route('places.destroy', parameters: $place) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger" onclick="return confirm('Biztos törölni szeretnéd ezt a várost?')">Delete</button>
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

</body>
</html>