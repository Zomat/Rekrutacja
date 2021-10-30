# Zadanie Rekrutacyjne 👋

## Instalacja 

Instalacja wszystkich zależności.
```sh
composer install
```

Uruchomienie migracji bazy danych razem z seederem uzupełniającym informacje o stolikach z pliku "seats.json"
```sh
php artisan migrate --seed
```

Uruchomienie serwera aplikacji.
```sh
php artisan serve
```


## Baza Danych
Aplikacja domyślnie używa bazy danych sqlite(możliwa zmiana na inną w pliku .env).

## Zapytanie do API
Zapytania api należy kierować na {ADRES_SERWERA}/api 

## Mailer
Do wysyłania "fake maili" używam Mailtrap.io, dane do przykładowej skrzynki są zapisane w pliku .env

## Uwagi
1. Jednostka używana w parametrze "duration" to minuta.
2. Laravel miewa problemy z obsługą parametrów Request Body w zapytaniach PUT i DELETE, dlatego najlepiej wysyłać je jako POST i dodać parametr "_method" o wartości "put" lub "delete".

