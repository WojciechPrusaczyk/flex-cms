# Wymagane oprogramowanie

- [MySql 10.4.32](https://www.apachefriends.org/pl/download.html), odstarczane wraz z pakietem XAMPP
- [node.js 22.12.0](https://nodejs.org/en/download)
- [npm 10.9.0](https://nodejs.org/en/download)
- [php 8.1.25](https://www.apachefriends.org/pl/download.html), odstarczane wraz z pakietem XAMPP
- [Composer](https://getcomposer.org/download/)
---

# Przygotowanie do instalacji

1. Należy utworzyć pustą bazę danych w MySql, o dowolnej nazwie, przyjmijmy natomiast jako przykład nazwę `artek-com-pl-database`
---
2. Po jej utworzeniu, trzeba uzupełnić connection string w pliku .env w katalogu głównym projektu:  
   Przykładowy connection string: `DATABASE_URL="mysql://root:@127.0.0.1:3306/artek-com-pl-database?serverVersion=10.4.27-MariaDB&charset=utf8mb4"`, gdzie:  
   Wyjaśnienie: `DATABASE_URL="mysql://{nazwa_uzytkownika}:@{adres_localhost}:{port}/{nazwa_bazy_danych}?serverVersion={wersja_mysql}-MariaDB&charset=utf8mb4"`
---
3. **Opcjonalnie**, można uzupełnić także zmienną obsługującą formularz kontaktowy, jednakże wymaga to podłączenia własnego klienta pocztowego, gdyż poczta od google, wp itp. zabezpiecza wysyłanie poczty za ich pośrednictwem:  
`MAILER_DSN=smtp://email_nadawcy:haslo_nadawcy@adres_serwera:port?verify_peer=1`
---
4. Po przygotowaniu bazy danych, trzeba wykonać serię komend, które stworzą niezbędne tabele i uzupełnią je wstępnymi danymi.
---
5. Pobranie i zainstalowanie niezbędnych paczek z pomocą composer: `composer install`
---
6. Przygotowanie migracji: `php bin/console make:migration`, **ważne!** należy jednak uprzednio się upewnić, że folder `/migrations` nie zawiera żadnych plików z rozszerzeniem PHP.
---
7. Rozpoczęcie procedury instalacji tabel: `php bin/console doctrine:migrations:migrate`  
**W razie problemów z instlacją, można usunąć wszystkie błędne tabele, wyczyścić folder** `/migrations`**, po czym spróbować ponownie.**
---
8. Zainstalowanie `fixtures`, czyli wstępnych danych do bazy danych.  
Przed załadowaniem `fixtures`, należy zainstalować odpowiednią paczkę do ładowania ich:  
`composer require doctrine/doctrine-fixtures-bundle --dev`.
Po pobraniu paczki, fixtures ładujemy do bazy danych z pomocą komendy:  
`php bin/console doctrine:fixtures:load`
---
9. Jeśli baza danych jest gotowa, to należy jeszcze skompilować wszystkie niezbędne pliki styli, zaczynając od komendy `npm install`.
---
10. po zainstalowaniu paczek node.js, należy skompilować wszystkie pliki stylów z pomocą komendy `encore dev`.
W przypadku problemów z użyciem, należy upewnić się, że encore jest [odpowiednio zainstalowany](https://symfony.com/doc/current/frontend/encore/installation.html).
---
11. System jest gotowy do pracy z podstawowymi danymi i wstępnym uzytkownikiem:  
Login: `john_doe`  
Hasło: `zaq1@WSX`
---
12. Uruchamianie serwera wymaga zainstalowanie [Symfony CLI](https://symfony.com/download), po udanej instalacji, powinno być możliwe użycie komendy `symfony`w konsoli.
---
13. Aby uruchomić server wystarczy użyć komendy `symfony server:start`, a do zatrzymania `symfony server:stop`
---
14. Po uruchomieniu, można dostać się do panelu administracyjnego po ścieżce `/login` 