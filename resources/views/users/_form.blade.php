<div class="space-y-4">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Imię *</label>
        <input type="text" name="name" required value="{{ old('name', $user->name) }}"
               class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        @error('name') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Email *</label>
        <input type="email" name="email" required value="{{ old('email', $user->email) }}"
               class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        @error('email') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">
            Hasło {{ $user->exists ? '(zostaw puste żeby nie zmieniać)' : '*' }}
        </label>
        <input type="password" name="password"
               class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
        <p class="mt-1 text-xs text-gray-500">Min. 8 znaków, litery + cyfry</p>
        @error('password') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Powtórz hasło</label>
        <input type="password" name="password_confirmation"
               class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none">
    </div>
</div>

<div class="mt-6 flex gap-3 justify-end">
    <a href="{{ route('users.index') }}" class="px-6 py-2 bg-gray-200 text-gray-800 font-bold rounded-lg hover:bg-gray-300">✖ Anuluj</a>
    <button type="submit" class="px-8 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700">💾 Zapisz</button>
</div>
