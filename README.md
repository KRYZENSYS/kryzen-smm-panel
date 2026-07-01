# 🚀 KRYZEN SMM Panel

**PHP + SQLite asosida qurilgan zamonaviy SMM xizmatlari boshqaruv paneli.**

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

## ⚡ Bepul deploy — 3 daqiqada tayyor

### 🟢 Variant 1: Render.com (tavsiya ✅)
[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/KRYZENSYS/kryzen-smm-panel)

1. Yuqoridagi tugmani bosing
2. GitHub bilan login
3. "Apply" — tayyor!
4. URL: `https://kryzen-smm.onrender.com`

### 🔵 Variant 2: Replit
1. [replit.com](https://replit.com) → "Create Repl"
2. "Import from GitHub" → `KRYZENSYS/kryzen-smm-panel`
3. Branch: `replit` ni tanlang
4. "Run" bosing
5. `/install.php` oching

### 🚂 Variant 3: Railway.app
1. [railway.app](https://railway.app) → "New Project"
2. "Deploy from GitHub repo"
3. `KRYZENSYS/kryzen-smm-panel` ni tanlang
4. Avtomatik deploy

## 🔐 Default login
| | |
|--|--|
| **Login** | `admin` |
| **Parol** | `admin123` |
| **Demo balans** | 100,000 so'm |

## ✨ Xususiyatlari

- 🔐 Login/Register (CSRF, bcrypt)
- 📊 Dashboard — buyurtma berish, tarix
- ⚡ 8 ta SMM xizmat (TikTok, Telegram, Instagram)
- 🛠 Admin Panel — foydalanuvchilar, buyurtmalar, API
- 🔌 7 ta API amali (balance, services, add, status, orders, cancel, refill)
- 💰 Balans tizimi
- 🎨 Tailwind CSS dark theme
- 🇺🇿 O'zbek tilida

## 📂 Branch'lar

| Branch | Maqsad |
|--|--|
| `main` | Production (MySQL/SQLite, Render.com uchun) |
| `replit` | Replit uchun maxsus (SQLite auto-setup) |

## 🛠 Lokal ishga tushirish

```bash
git clone https://github.com/KRYZENSYS/kryzen-smm-panel.git
cd kryzen-smm-panel
php -S localhost:8000
# http://localhost:8000/install.php
```

## 📜 Litsenziya
MIT — bemalol foydalaning.

## 🤝 Hissa qoʻshish
PR va issue'lar xush kelibsiz!

## 📞 Aloqa
- GitHub: [@KRYZENSYS](https://github.com/KRYZENSYS)
- Telegram: @kryzensys

---

⭐️ Yoqsa, star bosishni unutmang!
