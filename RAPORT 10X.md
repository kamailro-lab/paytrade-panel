# RAPORT 10X — pełny audyt projektu
## 10x-dealership / DealerHub / Paytrade / MRtardex

| | |
|---|---|
| **Repo** | https://github.com/kamailro-lab/10x-dealership |
| **Commit bazowy** | `c58e5c7` (main) |
| **Data audytu** | 18 lipca 2026 |
| **Prod** | `panel.mrtradex.ie` (cPanel) |
| **Stack** | Laravel 12 · PHP 8.2 · Breeze Blade · DomPDF · Vite 8 · Tailwind 3 · Alpine 3 |

---

## 1. Werdykt w jednym akapicie

Projekt ma działający rdzeń operacyjny dealera aut używanych w Irlandii: stock, zakup, koszty, sprzedaż, faktury VAT Margin Scheme, import z Google Sheets CSV, sync zdjęć z DealerHub.ie, lookup MotorCheck + Claude AI. Jako narzędzie dla 1–2 zaufanych osób — użyteczne. Jako system firmowy multi-user — **niegotowy**: krytyczne luki bezpieczeństwa, brak ról, niespójny model sprzedaży, chaos brandingu, prawie zerowe testy domenowe.

### Oceny

| Kategoria | Nota | Komentarz |
|-----------|------|-----------|
| Funkcje biznesowe | **7/10** | Pokrywa realny workflow dealera IE |
| Architektura | **6/10** | Prosto i czytelnie; dual sale + brak ACL |
| Bezpieczeństwo | **3/10** | Krytyczne dziury przed prod multi-user |
| Spójność | **4/10** | Brand, UI, płatności, „manager gate” |
| Testy / docs | **2/10** | Prawie nic domenowego |
| Gotowość produkcyjna | **4/10** | OK dla wąskiego zespołu; nie dla bezpiecznego systemu firmowego |

---

## 2. Mapa projektu (A → Z)

### 2.1 Co robi aplikacja

Wewnętrzny panel operacyjny dealera:
- rejestracja aut (rejestracja IE, dane techniczne, NCT, gotowość),
- zakup od dostawcy + koszty przygotowania,
- sprzedaż klientowi + płatności + gwarancja,
- faktury PDF (VAT Margin Scheme Irlandia),
- kontrahenci (supplier / customer),
- statystyki P&L (osobna bramka hasła menedżera),
- import historyczny z Google Sheets (CSV),
- wzbogacanie danych: DealerHub photos/API, MotorCheck scrape, Claude AI (opis / logbook).

### 2.2 Architektura

```
Browser → routes/web.php (auth) → Controllers → Models / Services
                                              ↓
                         DealerHub API · MotorCheck · Anthropic · DomPDF
                                              ↓
                                         MySQL / SQLite
```

**Brak:** `routes/api.php`, Jobs, Policies, Gates, ról użytkownika, dokumentacji produktowej.

### 2.3 Modele domenowe

```
User (standalone auth, bez ról)
Setting (key/value)

Vehicle 1──1 Purchase ──N:1 Contractor
       1──1 Sale     ──N:1 Contractor
       1──N Cost
Sale    1──1 Invoice
```

### 2.4 Kontrolery

| Kontroler | Rola |
|-----------|------|
| `VehicleController` | CRUD aut, enrich MotorCheck, sync DealerHub |
| `VehicleSaleController` | Nowa karta sprzedaży `/sell` |
| `SaleController` | **Legacy** inline sprzedaż (nadal żywy) |
| `PurchaseController` / `CostController` | Zakup i koszty |
| `ContractorController` | Kontrahenci |
| `InvoiceController` | Lista + generowanie PDF |
| `StatisticsController` | P&L za hasłem menedżera |
| `SettingsController` / `UserController` | Firma + pracownicy |
| `ImportController` | CSV Sheets |
| `VehicleLookupController` | JSON: decode reg, MotorCheck, Claude |
| Auth/* (Breeze) | Login, register, reset hasła |

### 2.5 Serwisy

| Serwis | Cel |
|--------|-----|
| `DealerHubFeedService` | Feed API stock + zdjęcia |
| `MotorCheckLookup` | Scrape free car check |
| `ClaudeAiParser` | Parse opisu / zdjęcia logbook |
| `IeRegistrationDecoder` | Parsowanie tablicy IE |
| `SheetsImporter` | Import CSV Stock/Sold |
| `VatMarginCalculator` | VAT = marża × 23/123 |
| `InvoiceGenerator` | DomPDF → storage |

### 2.6 Deploy

`.cpanel.yml` kopiuje drzewa do `/home/mrtradex/public_html/panel.mrtradex.ie/`, odpala migracje, usuwa `_migrate.php` / `_install.php` po deployu. Plik `_migrate.php` **nadal jest w repozytorium**.

---

## 3. Audyt bezpieczeństwa

### 3.1 KRYTYCZNE — naprawić natychmiast

#### C1. Publiczny skrypt migracji ujawnia własny token
- **Plik:** `public/_migrate.php`
- **Problem:** przy złym/brakującym `?token=` wypisuje poprawny token w HTML (`Your token: …`). Token jest przewidywalny (`paytrade-migrate-` + 8 hex z `md5(path+mtime)`). Po uzyskaniu tokenu: `migrate --force` + czyszczenie cache przez HTTP.
- **Deploy** usuwa plik po kopiowaniu, ale każdy świeży deploy z gita go przywraca do `public/` na chwilę (i plik wraca przy ręcznym deployu / błędzie skryptu).
- **Fix:** usunąć z repo na stałe; dodać do `.gitignore`; migracje tylko przez SSH/`artisan`.

#### C2. Otwarta rejestracja = pełny dostęp do systemu
- **Pliki:** `routes/auth.php`, `RegisteredUserController`
- **Problem:** każdy może `POST /register` i od razu: CRUD aut, użytkowników, ustawień firmy/IBAN, faktur, importu CSV, wywołań AI/DealerHub.
- **Fix:** wyłączyć register w produkcji; tworzenie kont tylko przez admina (`UserController`).

#### C3. Hardkodowane hasło menedżera
- **Plik:** `app/Http/Controllers/StatisticsController.php`
- **Kod:** `env('MANAGER_PASSWORD', 'menedzer2026')`
- **Problem:** jeśli env nieustawione → hasło `menedzer2026`. Brak w `.env.example`. Porównanie `===` (nie `hash_equals`). Brak rate-limitu → brute-force.
- **Fix:** fail-closed bez env; hash sekretu; throttle; lepiej prawdziwa rola `manager` w DB zamiast shared password.

### 3.2 WYSOKIE

#### H1. Brak RBAC / Policies
Każdy zalogowany pracownik może:
- zarządzać użytkownikami (reset haseł, usuwanie),
- zmieniać settings/IBAN firmy,
- usuwać auta / sprzedaże / zakupy,
- pobierać dowolne faktury,
- importować CSV (nadpisanie danych biznesowych),
- palić kredyty API (Anthropic / DealerHub).

FormRequest `authorize()` = tylko `$this->user() !== null`.

#### H2. „Ukryty zysk” jest nieszczelny
Intencja (komentarze w dashboard / StatisticsController): finanse tylko dla menedżera.  
Rzeczywistość: cena zakupu, total cost, marża / planned profit widoczne na:
- `resources/views/vehicles/index.blade.php`
- `resources/views/vehicles/show.blade.php`
- `resources/views/vehicles/sell.blade.php`
- wartość stocku na `dashboard.blade.php` (`$stockValue`, `$readyValue`)

#### H3. Słabe konta w seederze
`database/seeders/DatabaseSeeder.php`:
- `admin@cars.ie` / `password`
- `info@paytrade.ie` / `paytrade123`

Jeśli kiedykolwiek odpalone na prod — **natychmiast zrotować**.

#### H4. Hasło DB w process list (backup)
`app/Console/Commands/BackupDatabase.php` — `mysqldump ... -p%s` wrzuca hasło do argv (widoczne w `ps`).

### 3.3 ŚREDNIE

| ID | Problem | Gdzie |
|----|---------|-------|
| M1 | Brak throttle na AI / MotorCheck / DealerHub sync / import / manager login | `routes/web.php`, StatisticsController |
| M2 | Logowanie pełnych body odpowiedzi API | `ClaudeAiParser`, `DealerHubFeedService` |
| M3 | Exception message w JSON do klienta | `VehicleController@enrichSingle` |
| M4 | CSP z `'unsafe-inline'` + `'unsafe-eval'` | `SecurityHeaders.php` |
| M5 | PPSN klientów widoczne dla każdego staffa | Contractor show/edit |
| M6 | `.env.example` bez `MANAGER_PASSWORD`, `DEALERHUB_*`, `ANTHROPIC_*`; `APP_DEBUG=true` | `.env.example` |
| M7 | `.gitignore` nie ignoruje `_migrate.php` (ignoruje `_install.php`) | `.gitignore` |
| M8 | Photo URL w `onclick="...src='{{ $photoUrl }}'"` — ryzyko XSS w kontekście JS | `vehicles/show.blade.php` |
| M9 | npm audit: critical `shell-quote` (via concurrently), high Vite | `package-lock.json` (głównie tooling) |

### 3.4 NISKIE / INFO (pozytywne)

- Hasła hashowane (`Hash` / cast `hashed`).
- CSRF na formularzach i AJAX (`X-CSRF-TOKEN`).
- Brak `{!! !!}` w Blade.
- `SecurityHeaders` + force HTTPS (`.htaccess` + `AppServiceProvider`).
- Login rate-limit (5 prób) w `LoginRequest`.
- Upload logbook/CSV z walidacją MIME + size.
- `whereRaw` z bound parameters — OK.
- Invoice PDF przez auth controller, nie public disk.

### 3.5 Priorytet remediacji bezpieczeństwa

1. Usunąć `_migrate.php` z repo + potwierdzić na prod, że nie istnieje.
2. Wyłączyć `/register`.
3. Usunąć default `menedzer2026`; wymusić silne tajne lub role.
4. Zrotować hasła seederów jeśli użyte na żywo.
5. Dodać admin vs staff; ukryć dane finansowe przed staff.
6. Rate-limit manager login + endpointy AI/import.
7. Przestać logować body API; naprawić photo `onclick`; uzupełnić `.env.example`.

---

## 4. Audyt spójności (A → Z)

### 4.1 Branding — chaos nazw

| Miejsce | Nazwa |
|---------|-------|
| Sidebar layout | **DealerHub** Ireland |
| Guest / navigation (stary) | **Paytrade** |
| Seeder / faktury default | **Paytrade / MRtardex** |
| Deploy path | `mrtradex` / `panel.mrtradex.ie` |
| Repo GitHub | **10x-dealership** |
| `config('app.name')` fallback | PayTrade / Paytrade / Laravel |

**Rekomendacja:** jeden brand produktu (np. „Paytrade Panel”), a „DealerHub” tylko jako nazwa integracji zewnętrznej.

### 4.2 Sprzedaż — dwa równoległe światy

| Aspekt | Legacy (`SaleController` + form w show) | Nowy (`VehicleSaleController` + `/sell`) |
|--------|------------------------------------------|------------------------------------------|
| Route | `POST/DELETE vehicles/{v}/sale` | `GET/PUT/DELETE vehicles/{v}/sell` |
| Płatności | `payment_credit/bank/cash_deposit/trade` | `deposit` + `paid_cash` + `paid_bank` |
| Gwarancja | string `warranty` („12 MX Car protect”) | int `warranty_months` |
| Walidacja | inline w kontrolerze | `SaleRequest` |
| Faktura / import | używa starego modelu | — |
| Metody w modelu | `paymentTotal()` | `totalPaid()` |

`show.blade.php` nadal zawiera ukryty stary formularz (`sales.store`) **oraz** CTA do nowej karty `/sell`.

**Rekomendacja:** jeden flow — tylko `/sell`; usunąć `SaleController` i stary form; zmigrować dane; faktura + SheetsImporter na ten sam model.

### 4.3 Finanse vs „manager gate”

| Widok | Czy widać finanse bez hasła menedżera? |
|-------|----------------------------------------|
| `/statistics` | Nie (sesja `manager_auth`) |
| Dashboard — liczba aut | OK |
| Dashboard — wartość stocku (€) | **TAK — wyciek** |
| Lista aut — zakup / planned profit | **TAK** |
| Karta auta — koszt / marża | **TAK** |
| Karta sprzedaży — cena zakupu | **TAK** |
| Faktury — VAT / totals | **TAK** |

### 4.4 UI / theme

- Layout app: dark navy (Faza 1/2 „DealerHub style”).
- Lista aut: dark.
- `show` / `sell` / wiele formularzy: nadal light (`bg-gray-50`, białe karty, gray borders).
- Font: **Inter** (generyczny) — niespójne z ambicją brandowego UI.
- `navigation.blade.php` ma wyszarzone placeholdery „Faktury/Statystyki”, podczas gdy sidebar ma działające linki (martwy kod legacy).

### 4.5 i18n / locale

- UI po polsku (`lang/pl.json`, teksty w Blade).
- `.env.example`: `APP_LOCALE=en`.
- Mieszanka PL/EN w etykietach (gwarancja „12 MX…”, „No warranty”, faktury po angielsku — to częściowo uzasadnione dla IE).

### 4.6 Konfiguracja i sekrety

Używane w kodzie, **brak** w `.env.example`:
- `DEALERHUB_API_KEY`
- `DEALERHUB_DEALER_ID`
- `ANTHROPIC_API_KEY`
- `ANTHROPIC_MODEL`
- `MANAGER_PASSWORD`

### 4.7 Testy

| Suite | Stan |
|-------|------|
| Feature Auth/* | Breeze — OK |
| ProfileTest | OK |
| ExampleTest | **Prawdopodobnie FAIL** — oczekuje 200 na `/`, a `/` redirectuje na dashboard |
| Unit ExampleTest | Placeholder |
| Vehicles / Sale / VAT / Import / Stats / Users | **Brak** |

### 4.8 Dokumentacja

- `README.md` = szkielet Laravel (zero opisu produktu).
- Brak `docs/`, brak planu faz w repo.
- Jedyna „dokumentacja” = komentarze w serwisach + historia commitów („Faza 1”, „Faza 2”).

### 4.9 Integracje — ryzyka poza security

| Integracja | Ryzyko |
|------------|--------|
| MotorCheck | Scrape HTML — kruche, możliwe naruszenie ToS |
| DealerHub | Poprawne API; sync nadpisuje zdjęcia (source of truth) — OK jeśli świadome |
| Claude | Koszt + PII (logbook images) wysyłane na zewnątrz — brak polityki retencji |
| Sheets CSV | Import może masowo nadpisać dane — brak dry-run / preview w audycie ścieżki |

### 4.10 VAT Margin — spójność logiki

`VatMarginCalculator` i `Vehicle::totalCost()` / `margin()` liczą to samo (purchase + VRT + transport + costs). To jest **spójne**.  
Problem leży w warstwie płatności sprzedaży (dual fields), nie w samej formule VAT.

---

## 5. Co bym zmienił — nowy plan wdrożeniowy

### Faza 0 — Hardening (najpierw, zanim cokolwiek innego)
1. Usunąć `public/_migrate.php` z repo + `.gitignore`.
2. Wyłączyć publiczną rejestrację.
3. Usunąć default `menedzer2026`; wymusić `MANAGER_PASSWORD` lub (lepiej) kolumnę roli.
4. Zrotować hasła seederów / kont produkcyjnych.
5. Uzupełnić `.env.example` (puste placeholdery sekretów).
6. Potwierdzić na serwerze: brak `_migrate.php`, `APP_DEBUG=false`, `APP_ENV=production`, HTTPS cookie.

### Faza 1 — Jeden model sprzedaży
1. Kanoniczny flow: tylko `/vehicles/{id}/sell`.
2. Usunąć `SaleController` + ukryty form ze `show.blade.php`.
3. Zunifikować pola płatności (wybrać nowy lub stary zestaw — nie oba).
4. Jedna gwarancja: `warranty_months` + etykieta.
5. Przepisać `InvoiceGenerator` template + `SheetsImporter` na ten model.
6. Migracja danych: skopiować stare `payment_*` → nowe pola (lub odwrotnie) jednym skryptem.

### Faza 2 — Role i dane wrażliwe
1. Role: `admin` | `manager` | `staff`.
2. **Staff:** operacje stockowe bez cen zakupu / marży / VAT / IBAN / users / settings.
3. **Manager:** finanse + statystyki (bez shared password w env).
4. **Admin:** users, settings, import, sekrety.
5. Usunąć sesję `manager_auth` na rzecz `role` / Gate / Policy.
6. Ukryć wartość stocku na dashboardzie przed staff.

### Faza 3 — Produkt, brand, UI
1. Jeden brand w UI + `APP_NAME`.
2. Dokończyć dark theme na show/sell/formularzach **albo** świadomie jeden system kolorów.
3. README produktowe: setup, env, deploy cPanel, cron (`backup:db`, opcjonalnie `dealerhub:sync`).
4. `APP_LOCALE=pl` jeśli UI pozostaje po polsku.
5. Usunąć martwy `navigation.blade.php` / `welcome.blade.php` jeśli nieużywane.

### Faza 4 — Jakość i observability
1. Feature testy: sprzedaż, VAT margin, import, role, manager gate, DealerHub sync (mock HTTP).
2. Throttle na lookup/AI/import/statistics login.
3. Nie logować body API; generyczne błędy do klienta.
4. Backup bez hasła w argv (defaults-extra-file 0600).
5. CI: `php artisan test` + `composer audit` + `npm audit`.
6. Naprawić `ExampleTest` (oczekuj 302 na `/`).

---

## 6. Szybka checklista „czy możemy spać spokojnie?”

| Pytanie | Odpowiedź dziś |
|---------|----------------|
| Czy obcy może się zarejestrować? | **TAK** (`/register`) |
| Czy obcy może odpalić migracje przez WWW? | **Ryzyko** jeśli `_migrate.php` jest dostępny |
| Czy hasło menedżera jest w kodzie? | **TAK** (`menedzer2026`) |
| Czy pracownik widzi zysk bez hasła menedżera? | **TAK** (lista/karta auta) |
| Czy są role admin/staff? | **NIE** |
| Czy jest jeden model sprzedaży? | **NIE** (dwa) |
| Czy brand jest spójny? | **NIE** |
| Czy domena ma testy? | **NIE** |
| Czy HTTPS + security headers są? | **TAK** (z miękkim CSP) |
| Czy CSRF / hash haseł działają? | **TAK** |

---

## 7. Pliki kluczowe do przeglądu

```
public/_migrate.php                          ← CRITICAL
app/Http/Controllers/StatisticsController.php ← CRITICAL (default password)
routes/auth.php                              ← register
routes/web.php                               ← dual sale routes
app/Http/Controllers/SaleController.php      ← legacy
app/Http/Controllers/VehicleSaleController.php
app/Models/Sale.php                          ← dual payment fields
database/seeders/DatabaseSeeder.php          ← weak passwords
.env.example                                 ← missing secrets
resources/views/layouts/sidebar.blade.php    ← DealerHub brand
resources/views/vehicles/show.blade.php      ← light UI + finanse + stary form
.cpanel.yml                                  ← deploy + rm migrate
```

---

## 8. Podsumowanie dla decyzji biznesowej

**Zostawić i używać** jako wewnętrzny panel 1–2 osób — pod warunkiem natychmiastowego wyłączenia rejestracji, usunięcia `_migrate.php` i zmiany hasła menedżera.

**Nie skalować** na więcej pracowników / księgowość / zdalny dostęp bez Faz 0–2.

**Następny sensowny krok techniczny:** Faza 0 (1 PR hardening) → Faza 1 (jeden model sprzedaży) → Faza 2 (role).

---

*Raport wygenerowany automatycznie na podstawie przeglądu kodu repozytorium `10x-dealership` (branch main). Nie obejmuje testów penetracyjnych na żywym serwerze ani weryfikacji zawartości produkcyjnego `.env`.*
