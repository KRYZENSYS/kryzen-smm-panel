# 🚀 KRYZEN SMM Panel — Replit Edition

Bu branch **Replit uchun maxsus tayyorlangan** — SQLite bilan, MySQL kerak emas. Bir necha daqiqada ishlaydi.

## ⚡ Replit'da ishga tushirish (3 qadam)

### 1. Import qiling
1. [replit.com](https://replit.com) → ro'yxatdan o'ting
2. **"+ Create Repl"** bosing
3. **"Import from GitHub"** ni tanlang
4. URL: `https://github.com/KRYZENSYS/kryzen-smm-panel`
5. Branch: `replit` ni tanlang
6. **"Import"** bosing

### 2. Run bosing
Replit avtomatik PHP serverni ishga tushiradi. Yuqori paneldagi **"Run"** tugmasini bosing.

### 3. install.php oching
Brauzerda `/install.php` ni oching va "Admin yaratish" bosing.

**Tayyor login:**
- 👤 Login: `admin`
- 🔑 Parol: `admin123`

## 🌐 Sayt

Replit sizga avtomatik URL beradi:
```
https://kryzen-smm-panel.<sizning-username>.repl.co
```

## ✨ Replit branch afzalliklari

- ✅ **SQLite** — MySQL kerak emas, DB avtomatik yaratiladi
- ✅ **.replit** + **replit.nix** — PHP va SQLite kengaytmalari tayyor
- ✅ **Auto-install** — install.php birinchi marta o'zi admin yaratadi
- ✅ **Bepul** — Replit bepul rejada ishlaydi
- ✅ **5 daqiqada tayyor** — haqiqiy URL olasiz

## 🔌 API key
Default API key `database.sql` ichida. Admin panel → API Sozlamalari orqali o'zgartirish mumkin.

## 🔐 Xavfsizlik
- `install.php` ni admin yaratgandan keyin **o'chiring**
- Parolni **darhol** o'zgartiring (admin panel → Profil)
- Replit replit.co domenini beradi — maxfiy ma'lumot saqlamang

## 📋 Tuzilma

```
.
├── config.php          # SQLite auto-setup bilan
├── index.php           # Kirish nuqtasi
├── auth.php            # Login/Register
├── dashboard.php       # Foydalanuvchi paneli
├── services.php        # Xizmatlar ro'yxati
├── admin.php           # Admin panel
├── api_handler.php     # API proxy
├── install.php         # Birinchi admin (avtomatik)
├── services.json       # 8 ta SMM xizmat
├── .replit             # Replit config
├── replit.nix          # PHP+SQLite
└── database.sql        # MySQL uchun (ixtiyoriy, SQLite avtomatik)
```

## 🐛 Muammolar

**"Database error"** — Replit konsolida `php -r "var_dump(PDO::getAvailableDrivers());"` ishlatib, `sqlite` borligini tekshiring.

**"Port 8080 already in use"** — `.replit` faylidagi portni o'zgartiring.

**API ishlamayapti** — Admin panel → API Sozlamalari → to'g'ri URL va key kiriting.
