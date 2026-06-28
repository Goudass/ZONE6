# Adventure Blog — motyw WordPress (outdoor / GPX)

Minimalistyczny blog outdoorowy w języku polskim dla osób aktywnych szukających przygód.

## Uruchomienie lokalne (Docker)

**Wymagania:** [Docker Desktop](https://www.docker.com/products/docker-desktop/)

```bash
cd wordpress-blog
bash scripts/start-local.sh
```

Albo przez Makefile:

```bash
cd wordpress-blog
make up
```

Po starcie:

| Co | Adres |
|---|---|
| Strona | http://localhost:8080 |
| Panel admin | http://localhost:8080/wp-admin |
| Login | `admin` / `admin123` |

Skrypt automatycznie:
- instaluje WordPress
- aktywuje motyw **Adventure Blog**
- tworzy strony (Start, O mnie, Kontakt, Aktualności)
- konfiguruje menu
- dodaje przykładową trasę z GPX

**Zatrzymanie:**

```bash
docker compose down
```

**Reset (usunięcie bazy i danych):**

```bash
docker compose down -v
```

**Edycja motywu:** pliki w `wp-content/themes/adventure-blog/` — zmiany widoczne od razu po odświeżeniu strony.

**Zmiana portu / hasła:** skopiuj `.env.example` → `.env` i edytuj wartości.

---

## Co zawiera motyw

- **CPT „Trasa”** z formularzem w panelu admina (trudność, czas, dystans, przewyższenie, GPX, galeria)
- **3 poziomy trudności:** Łatwa / Średnia / Trudna
- **Mapa GPX** — Leaflet + OpenTopoMap (topografia, outdoor)
- **Wykres przewyższenia** — Chart.js, synchronizacja z mapą (hover)
- **Pobieranie pliku GPX**
- **Galeria** — karuzela Swiper
- **Formularz kontaktowy** wbudowany (bez wtyczki)
- **Social:** Instagram, Strava, Komoot (Customizer)
- **Font:** Montserrat
- **Placeholder logo** i przykładowe zdjęcia hero (Unsplash)
- **Animacje scroll** (fade-in)

## Wymagania

- WordPress 6.0+
- PHP 8.0+
- Hosting: Hostinger (lub dowolny z WordPress)

## Wdrożenie na Hostingerze

### 1. Instalacja WordPress

1. Zaloguj się do **hPanel → Websites → WordPress → Install**
2. Ustaw domenę, login admina i hasło
3. Po instalacji wejdź do **WP Admin**

### 2. Upload motywu

**Opcja A — ZIP (najprostsza)**

1. Spakuj folder `adventure-blog` do `adventure-blog.zip`
2. W WP Admin: **Wygląd → Motywy → Dodaj nowy → Wgraj motyw**
3. Aktywuj motyw **Adventure Blog**

**Opcja B — FTP / Menedżer plików**

1. Wgraj folder `adventure-blog` do:
   `public_html/wp-content/themes/adventure-blog`
2. Aktywuj motyw w panelu WordPress

### 3. Konfiguracja początkowa

#### Strona główna

1. **Ustawienia → Czytanie**
2. Strona główna wyświetla: **Strona statyczna**
3. Strona główna: utwórz stronę „Start” (pusta treść — motyw użyje `front-page.php`)
4. Wpisów na blogu: utwórz stronę „Aktualności”

#### Permalinki

1. **Ustawienia → Bezpośrednie odnośniki → Nazwa wpisu**
2. Zapisz

#### Menu

1. **Wygląd → Menu**
2. Utwórz menu główne z linkami:
   - O mnie → `/o-mnie/`
   - Aktualności → `/aktualnosci/`
   - Trasy rowerowe → `/typ-trasy/trasy-rowerowe/`
   - Tatry → `/typ-trasy/tatry/`
   - Projekty → `/typ-trasy/projekty/`
   - Kontakt → `/kontakt/`
3. Przypisz do lokalizacji **Menu główne**

#### Strony statyczne

Utwórz strony (treść dowolna / placeholder):

| Slug | Tytuł |
|------|-------|
| `o-mnie` | O mnie |
| `kontakt` | Kontakt |

Strona **Kontakt** automatycznie wyświetli formularz (slug musi być `kontakt`).

#### Customizer

**Wygląd → Dostosuj → Blog outdoorowy**

- Nazwa bloga (tymczasowa)
- Nagłówek i podtytuł hero
- Zdjęcie hero (URL)
- Instagram / Strava / Komoot
- Email kontaktowy

### 4. Dodawanie trasy (krok po kroku)

1. **Trasy → Dodaj trasę**
2. Wpisz tytuł i opis trasy
3. Ustaw **Typ trasy** (Trasy rowerowe / Tatry / Projekty)
4. W meta boxie **Szczegóły trasy**:
   - Trudność (Łatwa / Średnia / Trudna)
   - Czas, dystans, przewyższenie
   - Plik GPX (upload)
   - Galeria zdjęć
5. Ustaw **miniaturę** (zdjęcie główne)
6. Opublikuj

Przykładowy plik GPX: `sample-data/sample-route.gpx`

### 5. Formularz kontaktowy

Motyw ma **wbudowany formularz** na stronie `/kontakt/` — działa od razu przez `wp_mail()`.

**Rekomendacja na później (opcjonalnie):** wtyczka **Contact Form 7** lub **WPForms Lite**, jeśli chcesz:
- reCAPTCHA anty-spam
- więcej pól
- integrację z newsletterem

Na start wbudowany formularz jest wystarczający.

### 6. SEO (zalecane wtyczki)

- **Rank Math** lub **Yoast SEO** — meta, sitemap, Open Graph
- **LiteSpeed Cache** (jeśli Hostinger ma LiteSpeed) — szybkość

## Struktura plików motywu

```
adventure-blog/
├── style.css
├── functions.php
├── front-page.php
├── single-trasa.php
├── archive-trasa.php
├── taxonomy-typ-trasy.php
├── page.php
├── single.php
├── inc/
│   ├── cpt-trasa.php
│   ├── meta-boxes.php
│   ├── enqueue.php
│   ├── customizer.php
│   ├── contact-form.php
│   └── template-tags.php
├── template-parts/
│   ├── route-card.php
│   └── content-card.php
├── assets/
│   ├── css/main.css
│   ├── js/
│   │   ├── main.js
│   │   ├── gpx-route.js
│   │   └── admin-route.js
│   └── images/logo-placeholder.svg
└── sample-data/sample-route.gpx
```

## Paleta kolorów

| Rola | Hex |
|------|-----|
| Tło główne | `#D1D2D3` |
| Tło ciemne | `#151B13` |
| Akcent | `#373D2E` |
| Tekst drugorzędny | `#66665A` |
| Obramowania | `#999A96` |

## Co ustawisz później

- **Nazwa bloga** — Customizer → Blog outdoorowy
- **Logo** — Customizer → Tożsamość witryny → Logo (zastąpi placeholder)
- **Własne zdjęcia hero** — Customizer lub miniatury tras

## Licencja

Motyw stworzony na potrzeby projektu bloga outdoorowego.
