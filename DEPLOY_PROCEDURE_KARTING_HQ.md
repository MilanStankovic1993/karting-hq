# Karting HQ – DEPLOY PROCEDURA (STEP BY STEP)

Ovaj dokument opisuje **tačnu proceduru isporuke** Karting HQ aplikacije
– od lokalnog računara (PowerShell) do produkcionog servera.

--------------------------------------------------
## 0. PREDUSLOVI
--------------------------------------------------
- Kod je commitovan i pushovan na GitHub (branch: main)
- Server je već inicijalno podešen (PHP, Nginx, DB, SSH)
- Deploy skripta postoji na serveru:
  /var/www/karting-hq/app/deploy.sh
- Root wrapper:
  /usr/local/bin/karting-deploy

--------------------------------------------------
## 1. LOKALNO (Windows – PowerShell)
--------------------------------------------------

### 1.1 Otvori PowerShell
```powershell
cd C:\putanja\do\projekta\karting-hq

1.2 Proveri status
git status

1.3 Commit izmene
git add .
git commit -m "Opis izmene"

1.4 Push na GitHub
git push origin main

2. LOGIN NA SERVER
2.1 SSH konekcija
ssh root@IP_ADRESA_SERVERA


Primer:

ssh root@91.98.174.175

3. STANDARDNA ISPORUKA (BEZ BRISANJA BAZE)

Koristi se u 99% slučajeva.

karting-deploy


Ova komanda automatski:

radi git pull

pokreće composer install

radi php artisan migrate --force

čisti i rebuild-uje cache

restartuje PHP-FPM i reload-uje nginx

4. ISPORUKA SA RESETOM BAZE (SAMO KAD TREBA)

⚠️ OVO BRIŠE SVE TABELE U BAZI ⚠️
Koristi se samo:

kad menjaš migracije

kad menjaš seedere

u ranoj fazi projekta

karting-deploy fresh


Ova komanda radi:

migrate:fresh --seed --force

ponovo ubacuje admin korisnike

rebuild cache

restart servisa

5. PROVERA POSLE DEPLOY-A
5.1 Provera aplikacije

U browseru:

http://IP_ADRESA_SERVERA/admin


Primer:

http://91.98.174.175/admin

5.2 Provera user-a u bazi (opciono)
sudo -u deploy -H bash -lc '
cd /var/www/karting-hq/app &&
php artisan tinker --execute="
dump(
  \App\Models\User::select(
    \"email\",\"username\",\"role\",\"is_active\",\"team_id\"
  )->get()->toArray()
);
"
'

6. AKO NEŠTO NE RADI
6.1 Proveri Laravel log
tail -n 200 /var/www/karting-hq/app/storage/logs/laravel.log

6.2 Proveri servise
systemctl status php8.3-fpm || systemctl status php8.2-fpm
systemctl status nginx

7. ZLATNA PRAVILA

❌ Nikad ne radi migrate:fresh ako ima produkcionih podataka

✅ Uvek koristi karting-deploy

✅ Seederi moraju imati is_active = true

✅ Super admin može imati team_id = null

✅ Posle svake isporuke proveri login

8. KRATAK PODSETNIK (CHEAT SHEET)

PowerShell:

git add .
git commit -m "msg"
git push origin main
ssh root@SERVER_IP


Server:

karting-deploy
# ili
karting-deploy fresh


Kraj dokumenta.


---

Ako želiš, mogu ti:
- 📄 prevesti ovo u **Word / PDF**
- 🧩 dodati **sekciju za rollback**
- 🔐 dodati **multi-server deploy (staging → prod)**
- 🤖 dodati **GitHub Actions kasnije**

Ali ovo što imaš sada je **čista, profesionalna deploy procedura** 👌