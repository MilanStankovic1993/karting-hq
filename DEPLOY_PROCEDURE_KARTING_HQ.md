# Karting HQ – DEPLOY PROCEDURA (STEP BY STEP)

Ovaj dokument opisuje **tačnu i proverenu proceduru isporuke (deploy)** aplikacije  
**Karting HQ**, od lokalnog računara (Windows / PowerShell) do produkcionog servera
(Hetzner / Ubuntu / Nginx / PHP-FPM).

Dokument je pisan po realnom scenariju i **odrađen je end-to-end u produkciji**.

---

## 0. PREDUSLOVI

Pre nego što započneš deploy, mora biti ispunjeno sledeće:

- Kod je **commitovan i pushovan** na GitHub (`main` branch)
- Server je inicijalno podešen:
  - PHP 8.2 ili 8.3
  - Nginx
  - MySQL
  - SSH pristup
- GitHub SSH autentikacija radi (`ssh -T git@github.com`)
- Aplikacija se nalazi na putanji:
/var/www/karting-hq/app

diff
Copy code
- Postoji deploy wrapper:
/usr/local/bin/karting-deploy

yaml
Copy code

---

## 1. LOKALNO (Windows – PowerShell)

### 1.1 Otvori PowerShell i uđi u projekat

```powershell
cd C:\putanja\do\projekta\karting-hq
1.2 Proveri status izmene
powershell
Copy code
git status
1.3 Commit izmene
powershell
Copy code
git add .
git commit -m "Opis izmene"
1.4 Push na GitHub
powershell
Copy code
git push origin main
2. LOGIN NA SERVER
2.1 SSH konekcija
bash
Copy code
ssh root@IP_ADRESA_SERVERA
Primer:

bash
Copy code
ssh root@91.98.174.175
3. STANDARDNA ISPORUKA (BEZ BRISANJA BAZE)
✅ Ovo je podrazumevana procedura (99% slučajeva)
❌ Ne briše bazu
❌ Ne dira postojeće podatke

Pokreće se jednom komandom:

bash
Copy code
karting-deploy
Šta ova komanda radi:
git pull

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan optimize:clear

php artisan config:cache

php artisan route:cache

php artisan view:cache

restartuje PHP-FPM

reload-uje Nginx

4. ISPORUKA SA RESETOM BAZE (SAMO KAD TREBA)
⚠️ OVO BRIŠE SVE TABELE U BAZI ⚠️
Koristi se samo u sledećim situacijama:

menjaš migracije

menjaš seedere

inicijalni deploy

rani razvoj (bez produkcionih podataka)

Komanda:
bash
Copy code
karting-deploy fresh
Ova komanda radi:
php artisan migrate:fresh --seed --force

ponovo kreira admin korisnike

rebuild-uje cache

restartuje servise

5. PROVERA POSLE DEPLOY-A
5.1 Provera aplikacije u browseru
Otvori:

pgsql
Copy code
http://IP_ADRESA_SERVERA/admin/login
Primer:

pgsql
Copy code
http://91.98.174.175/admin/login
Ako se dashboard učita → ✅ deploy je uspešan.

5.2 Provera admin korisnika (opciono – CLI)
bash
Copy code
sudo -u deploy -H bash -lc '
cd /var/www/karting-hq/app &&
php artisan tinker --execute="
dump(
  \App\Models\User::select(
    \"email\",
    \"username\",
    \"role\",
    \"is_active\",
    \"team_id\"
  )->get()->toArray()
);
"
'
Očekivano:

role = SUPER_ADMIN

is_active = true

team_id = null (dozvoljeno)

6. AKO NEŠTO NE RADI
6.1 Laravel log
bash
Copy code
tail -n 200 /var/www/karting-hq/app/storage/logs/laravel.log
6.2 Status servisa
bash
Copy code
systemctl status php8.3-fpm || systemctl status php8.2-fpm
systemctl status nginx
7. ZLATNA PRAVILA
❌ Nikad ne koristi migrate:fresh ako ima produkcionih podataka
✅ Uvek koristi karting-deploy
✅ Seederi moraju postaviti is_active = true
✅ Super admin može imati team_id = null
✅ Posle svakog deploy-a proveri login
✅ Ako dobiješ 403 – prvo proveri canAccessPanel()

8. KRATAK PODSETNIK (CHEAT SHEET)
PowerShell (lokalno)
powershell
Copy code
git add .
git commit -m "msg"
git push origin main
ssh root@SERVER_IP
Server
bash
Copy code
karting-deploy
# ili (samo kad znaš šta radiš)
karting-deploy fresh
Kraj dokumenta.

markdown
Copy code

---

Ako želiš sledeći korak možemo:
- 📄 eksportovati ovo u **Word / PDF**
- 🔁 dodati **rollback proceduru**
- 🤖 napraviti **GitHub Actions deploy**
- 🧪 dodati **pre-deploy validator (DB, ENV, panel access)**

Ali ovo što sada imaš je **100% ispravan, profesionalan deploy vodič** 💪