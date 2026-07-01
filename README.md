# 🚀 KRYZEN SMM Panel

**KRYZEN SMM** — to‘liq funktsional SMM xizmatlari boshqaruv tizimi. PHP + MySQL + Tailwind CSS asosida qurilgan zamonaviy admin panel.

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

## ✨ Xususiyatlari

- 🔐 **Autentifikatsiya** — login/register, CSRF himoya, sessiya boshqaruvi
- 📊 **Dashboard** — balans, yangi buyurtma, buyurtmalar tarixi
- ⚡ **8 ta xizmat** — TikTok, Telegram, Instagram (ko‘rishlar, obunachilar, reaksiyalar)
- 🛠 **Admin Panel** — foydalanuvchilar, buyurtmalar, API sozlamalari, balans boshqaruvi
- 🔌 **API Proxy** — CORS muammosiz, 7 ta API amali (balance, services, add, status, orders, cancel, refill)
- 💰 **Balans tizimi** — har bir buyurtmadan avtomatik yechish
- 🎨 **Zamonaviy UI** — Tailwind CSS, dark theme, responsive
- 🇺🇿 **O‘zbek tilida** — to‘liq lokalizatsiya

## 📦 O‘rnatish

### 1. Reponi klonlash
```bash
git clone https://github.com/KRYZENSYS/kryzen-smm-panel.git
cd kryzen-smm-panel
```

### 2. Ma’lumotlar bazasini yaratish
```sql
mysql -u root -p < database.sql
```

### 3. Konfiguratsiya
`config.php` faylida DB sozlamalarini tahrirlang:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kryzen_smm');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Veb-server ishga tushirish
```bash
php -S localhost:8000
```
Yoki Apache/Nginx orqali `htdocs` papkasiga tashlang.

### 5. Birinchi admin yaratish
SQL orqali:
```sql
INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@kryzen.uz', '$2y$10$...', 'admin');
```

## 🗂 Fayl tuzilmasi

```
kryzen-smm-panel/
├── index.php          # Asosiy kirish nuqtasi
├── auth.php           # Login & Register
├── dashboard.php      # Foydalanuvchi paneli
├── services.php       # Barcha xizmatlar
├── admin.php          # Admin paneli
├── api_handler.php    # API proxy (7 ta amal)
├── config.php         # Konfiguratsiya
├── layout_top.php     # Header + Sidebar
├── layout_bottom.php  # Footer + JS
├── logout.php         # Tizimdan chiqish
├── database.sql       # DB sxema
├── services.json      # Xizmatlar katalogi
└── .gitignore
```

## 🔌 API amallari

`api_handler.php` orqali:
- `balance` — API balansni olish
- `services` — xizmatlar ro‘yxati
- `add` — yangi buyurtma
- `status` — buyurtma holati
- `orders` — foydalanuvchi buyurtmalari
- `cancel` — bekor qilish
- `refill` — qayta to‘ldirish

## 🛠 Texnologiyalar

- **Backend:** PHP 7.4+ (PDO, MySQL)
- **Frontend:** Tailwind CSS (CDN), Vanilla JS
- **Database:** MySQL 5.7+
- **API:** REST (JSON, application/x-www-form-urlencoded)

## 🔒 Xavfsizlik

- ✅ CSRF token himoyasi
- ✅ Password hashing (bcrypt)
- ✅ SQL Injection himoyasi (PDO prepared statements)
- ✅ XSS himoyasi (htmlspecialchars)
- ✅ Session fixation himoyasi
- ✅ `.htaccess` bilan papka yopish (ixtiyoriy)

## 📜 Litsenziya

MIT License — bemalol foydalaning va o‘zgartiring.

## 🤝 Hissa qo‘shish

Pull requestlar xush kelibsiz! Katta o‘zgarishlar uchun avval issue oching.

## 📞 Aloqa

- **Telegram:** @kryzensys
- **Email:** support@kryzen.uz
- **Sayt:** https://kryzen.uz

---

⭐️ Loyiha yoqsa, star bosishni unutmang!
