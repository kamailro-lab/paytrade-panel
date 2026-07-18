# Audyt spójności i bezpieczeństwa — paytrade-panel

| | |
|---|---|
| **Repo (faktyczne)** | https://github.com/kamailro-lab/paytrade-panel |
| **Branch bazowy** | `main` @ `c58e5c7` |
| **Data** | 18 lipca 2026 |
| **Prod** | `panel.mrtradex.ie` (cPanel / Hetzner path w `.cpanel.yml`) |
| **Stack** | Laravel 12 · Blade/Breeze · MySQL · Vite/Tailwind |
| **Zakres** | Spójność kontekstu + weryfikacja bezpieczeństwa kodu |
| **Poza zakresem** | `10x-dealership` (Slim PHP + React + PostgreSQL RLS) — **nie jest w tym workspace** |

---

## 0. Werdykt

1. **Spójność kontekstu poprzedniego raportu (`RAPORT 10X.md`) — NIESPÓJNY.**  
   Opisywał repo jako `10x-dealership`, a audytował i pushował do `paytrade-panel`. Treść techniczna (Laravel, cPanel, `panel.mrtradex.ie`) dotyczy PayTrade, nie 10x.
2. **Bezpieczeństwo `paytrade-panel` — 3 krytyki z poprzedniego raportu POTWIERDZONE** na `main` @ `c58e5c7`.
3. **Spójność produktu** — branding i model sprzedaży niespójne (DealerHub vs Paytrade vs MRtardex); dane finansowe „ukryte” tylko pozornie.

**Ocena:** bezpieczeństwo **3/10**, spójność kontekstu poprzedniego raportu **1/10**, spójność produktu **4/10**.

---

## 1. Audyt spójności kontekstu (meta)

### 1.1 Co pokazuje zrzut / porównanie

| Pole | Intencja (10x-dealership) | Faktyczny target raportu |
|------|---------------------------|---------------------------|
| Repo | `kamailro-lab/10x-dealership` | `kamailro-lab/paytrade-panel` |
| Commit | `25826ce…` | `c58e5c7` (istnieje tylko w paytrade-panel) |
| Stack | Slim PHP + React/Vite + PostgreSQL RLS | Laravel 12 + Blade + MySQL |
| Deploy | VPS / domena dealership | cPanel `panel.mrtradex.ie` |
| Branch raportu | — | `cursor/raport-audytu-10x-620d` (nazwa myląca) |

### 1.2 Dowody w Cursor Cloud

| Agent | Repo URL | Branch |
|-------|----------|--------|
| „Kompleksowy audyt projektu” (`bc-1b37d6a9-…620d`) | `paytrade-panel` | `cursor/raport-audytu-10x-620d` |
| Ten audyt | `paytrade-panel` | `cursor/audyt-spojnosci-bezpieczenstwa-785d` |

Poprzedni agent w myśleniu zauważył rozjazd (`paytrade-panel` ≠ `10x-dealership`), ale **nie poprawił nagłówka raportu** — nadal wpisał `https://github.com/kamailro-lab/10x-dealership` i commit `c58e5c7` (należący do innego repo).

### 1.3 Co jest poprawne w poprzednim raporcie mimo złej etykiety

Treść merytoryczna (kontrolery, `_migrate.php`, seeder, branding DealerHub/Paytrade, cPanel) **pasuje do paytrade-panel**.  
Nie pasuje do stacku 10x (brak `api/public/index.php`, brak React `frontend/`, brak PostgreSQL RLS).

### 1.4 Ryzyko informacyjne ze zrzutu ekranu (meta-security)

Zrzut / notatka porównawcza ujawnia m.in.:
- produkcyjny IP VPS projektu 10x,
- lokalną ścieżkę użytkownika,
- domeny i hosting obu projektów.

**Rekomendacja:** nie wrzucać IP / ścieżek lokalnych do publicznych PR i raportów w repo; w kolejnych promptach podawać agentowi **jawnie**: repo URL + branch + commit + stack.

---

## 2. Bezpieczeństwo — weryfikacja (paytrade-panel @ c58e5c7)

### 2.1 KRYTYCZNE (potwierdzone)

#### C1. `public/_migrate.php` ujawnia token i odpala migracje

- **Plik:** `public/_migrate.php:13–20`
- Przy złym/brakującym `?token=` wypisuje poprawny token w HTML.
- Token: `paytrade-migrate-` + 8 hex z `md5(__FILE__ . filemtime(...))`.
- Po tokenie: `migrate --force` + czyszczenie cache przez HTTP.
- `.cpanel.yml:11` usuwa plik **po** deployu — okno ryzyka przy każdym świeżym `cp -R public`.

**Fix:** usunąć z repo + `.gitignore`; migracje tylko SSH/`artisan`; potwierdzić brak pliku na prod.

#### C2. Otwarta rejestracja = pełny dostęp

- **Pliki:** `routes/auth.php:14–18`, `RegisteredUserController`
- `GET/POST /register` dla gości → natychmiastowy dostęp do CRUD aut, users, settings/IBAN, faktur, importu, AI/DealerHub.

**Fix:** wyłączyć register w produkcji; konta tylko przez admina.

#### C3. Domyślne hasło menedżera `menedzer2026`

- **Plik:** `app/Http/Controllers/StatisticsController.php:42`
- `env('MANAGER_PASSWORD', 'menedzer2026')` + porównanie `===`, brak throttle.
- Przy `config:cache` `env()` poza config często zwraca `null` → **zawsze default**.

**Fix:** fail-closed bez sekretu; `config()`; `hash_equals` / hash; throttle; docelowo rola w DB.

### 2.2 WYSOKIE (potwierdzone)

| ID | Problem | Evidencja |
|----|---------|-----------|
| H1 | Brak RBAC / Policies / Gates | `routes/web.php` — tylko `auth`; brak `app/Policies` |
| H2 | „Ukryty zysk” nieszczelny | ceny/marże na listach/szczegółach aut mimo komentarzy o statistics |
| H3 | Słabe hasła w seederze | `admin@cars.ie`/`password`, `info@paytrade.ie`/`paytrade123` |
| H4 | Hasło DB w argv backupu | `BackupDatabase` → `mysqldump -p…` |

### 2.3 ŚREDNIE / NISKIE (skrót)

- Brak throttle na AI / MotorCheck / manager login / import  
- CSP z `'unsafe-inline'` / `'unsafe-eval'`  
- PPSN widoczne dla każdego staffa  
- `.env.example`: `APP_DEBUG=true`, brak `MANAGER_PASSWORD` / kluczy integracji  
- DOM XSS ryzyko: `innerHTML` z danymi MotorCheck w `_form.blade.php`  
- Pozytywne: CSRF, brak `{!! !!}`, login throttle, SecurityHeaders + HTTPS force, Eloquent/parametryzowane zapytania

### 2.4 Priorytet remediacji

1. Usunąć `_migrate.php` (repo + prod).  
2. Wyłączyć `/register`.  
3. Usunąć default `menedzer2026`; ustawić silny sekret lub role.  
4. Zrotować hasła seederów jeśli kiedykolwiek na prod.  
5. Admin vs staff + ukrycie finansów.  
6. Throttle + hardening session/CSP.

---

## 3. Spójność produktu (paytrade-panel)

### 3.1 Branding — chaos nazw

| Miejsce | Nazwa |
|---------|-------|
| Sidebar | **DealerHub** Ireland |
| Guest / stara nawigacja | **Paytrade** |
| Seeder / faktury default | **Paytrade / MRtardex** |
| Deploy | `mrtradex` / `panel.mrtradex.ie` |
| `config('app.name')` fallbacki | PayTrade / Paytrade / Laravel |
| Integracja zewnętrzna | DealerHub.ie (OK jako nazwa API) |

**Rekomendacja:** jeden brand UI (np. „Paytrade Panel”); „DealerHub” tylko przy syncu zewnętrznym.

### 3.2 Model sprzedaży — dual flow

- Nowa karta: `VehicleSaleController` (`/vehicles/{id}/sell`)
- Legacy: `SaleController` (`POST vehicles/{id}/sale`) — nadal w routach

Ryzyko niespójnych danych i różnego UX.

### 3.3 Dokumentacja

- `README.md` = szablon Laravel (zero opisu produktu).  
- Poprzedni `RAPORT 10X.md` na PR#1 myląco etykietuje obce repo — **nie traktować jako źródła prawdy o 10x-dealership**.

---

## 4. Relacja do 10x-dealership

| | 10x-dealership | paytrade-panel (ten audyt) |
|--|----------------|----------------------------|
| Backend | Slim, `api/public/index.php` | Laravel 12 |
| Frontend | React/Vite `frontend/` | Blade + Alpine/Vite |
| DB | PostgreSQL + RLS | MySQL |
| Hosting | VPS / 10xdealership.com | cPanel panel.mrtradex.ie |

**Ten raport nie audytuje 10x-dealership.**  
Aby audytować 10x: uruchomić agenta na repo `kamailro-lab/10x-dealership`, branch `main`, commit `25826ce…`, ze stackiem Slim+React+PostgreSQL w prompcie.

---

## 5. Podsumowanie dla decyzji

| Pytanie | Odpowiedź |
|---------|-----------|
| Czy poprzedni „RAPORT 10X” celował w złe repo? | **Tak** (etykieta 10x, kod PayTrade). |
| Czy 3 krytyki są realne? | **Tak** — potwierdzone w kodzie. |
| Czy naprawiać teraz paytrade-panel? | Tak, jeśli panel jest w użyciu produkcyjnym. |
| Czy wracać do backlogu 10x? | Osobny agent / osobne repo — ten workspace to nie 10x. |

---

*Raport wygenerowany na podstawie kodu `paytrade-panel` @ `c58e5c7` oraz metadanych Cursor Cloud poprzedniego agenta audytu. Nie obejmuje testów penetracyjnych na żywym serwerze ani odczytu produkcyjnego `.env`.*
