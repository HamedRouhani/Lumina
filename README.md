# 🤖 لومینا - سیستم مدیریت چت‌بات هوشمند

یک پنل مدیریت کامل برای راه‌اندازی و مدیریت چت‌بات‌های هوشمند مبتنی بر **OpenAI Assistants API** با قابلیت تخصیص به مشتریان، مدیریت اشتراک و ویجت قابل نصب روی وب‌سایت.

---

## ✨ قابلیت‌های کلیدی

- **مدیریت کاربران (ادمین)**: سطوح دسترسی `super_admin`، `admin` و `moderator`
- **مدیریت مشتریان**: اضافه، ویرایش، فعال/غیرفعال کردن مشتریان
- **مدیریت سرویس‌ها**: ایجاد سرویس‌های چت‌بات با `assistant_id` اختصاصی از OpenAI
- **سیستم اشتراک (Subscription)**: تعیین محدودیت تعداد چت و مدت زمان اعتبار
- **تولید کد ویجت**: تولید خودکار کدهای `Floating` و `iFrame` با تنظیمات ظاهری سفارشی
- **پنل ناظر (Moderator)**: مشاهده سرویس‌های مرتبط، کد ویجت و گزارش استفاده کاربران
- **گزارش‌گیری**: نمایش آمار چت‌ها، فعالیت‌های اخیر و خروجی اکسل
- **استریم پاسخ OpenAI**: دریافت پاسخ‌های لحظه‌ای (Streaming) از OpenAI Assistants

---

## 🛠️ تکنولوژی‌های استفاده شده

- **PHP 7.4+** (MVC سفارشی بدون فریمورک)
- **MySQL** + **PDO**
- **OpenAI Assistants API** (v2)
- **JavaScript Vanilla** (بدون کتابخانه‌ی خارجی)
- **Chart.js** (برای نمایش نمودارها)
- **HTML5 + CSS3** (ریسپانسیو)

---

## 📁 ساختار پروژه

```
mylumina/
├── .htaccess              # مسیریابی و امنیت
├── index.php              # ورودی اصلی و مسیریابی (Router)
├── config.php             # تنظیمات (اختیاری)
├── app/
│   ├── core/              # هسته‌ی اصلی
│   │   ├── Controller.php
│   │   ├── Model.php
│   │   ├── Database.php
│   │   └── Router.php
│   ├── controllers/       # کنترلرها
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── CustomerController.php
│   │   ├── DashboardController.php
│   │   ├── ModeratorController.php
│   │   ├── ServiceController.php
│   │   ├── SubscriptionPlanController.php
│   │   └── WidgetController.php
│   ├── models/            # مدل‌ها
│   │   ├── AdminModel.php
│   │   ├── ChatModel.php
│   │   ├── CustomerModel.php
│   │   ├── ServiceModel.php
│   │   ├── ServiceSubscriptionModel.php
│   │   ├── SubscriptionPlanModel.php
│   │   └── UsageReportModel.php
│   ├── middleware/        # میان‌افزارها
│   ├── helpers/           # توابع کمکی
│   │   ├── functions.php
│   │   └── widget_helper.php
│   └── views/             # قالب‌ها (View)
│       ├── admin/
│       ├── moderator/
│       ├── auth/
│       ├── dashboard/
│       ├── layouts/
│       └── widget/
├── widgets/               # فایل‌های ویجت (اختیاری)
├── assets/                # فایل‌های CSS/JS/تصاویر
└── .env                   # متغیرهای محیطی (حساس)
```

---

## 🚀 راه‌اندازی (نصب)

### ۱. پیش‌نیازها

- سرور وب (Apache / Nginx)
- PHP 7.4 یا بالاتر
- MySQL 5.7 یا بالاتر
- دسترسی به `mod_rewrite` در Apache (برای `.htaccess`)
- حساب **OpenAI** با دسترسی به **Assistants API**

### ۲. مراحل نصب

```bash
# ۱. کلون کردن مخزن
git clone https://github.com/your-username/lumina.git
cd lumina

# ۲. تنظیم مجوز پوشه‌ها (در صورت نیاز)
chmod -R 755 app/ widgets/ assets/

# ۳. کپی فایل .env و تنظیم اطلاعات
cp .env.example .env
```

### ۳. تنظیمات دیتابیس

فایل `.env` را باز کنید و اطلاعات زیر را تکمیل کنید:

```env
DB_HOST=localhost
DB_NAME=lumina_db
DB_USER=db_user
DB_PASSWORD=db_password

OPENAI_API_KEY=sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_URL=https://your-domain.com/mylumina
```

> ⚠️ فایل `.env` را هرگز در مخزن گیت قرار ندهید (در `.gitignore` قرار دارد).

### ۴. ایمپورت دیتابیس

فایل اسکیما (ساختار جداول) را از پوشه‌ی `database/` در دیتابیس خود ایمپورت کنید:

```sql
mysql -u db_user -p lumina_db < database/schema.sql
```

> اگر فایل `schema.sql` وجود ندارد، از ساختار جداول موجود در مدل‌ها استفاده کنید یا با توجه به کدها جداول را بسازید.

---

## 🔐 نقش‌های کاربری

| نقش | توضیح |
|---|---|
| `super_admin` | دسترسی کامل به همه‌ی بخش‌ها |
| `admin` | دسترسی به مدیریت مشتریان، سرویس‌ها و کاربران (به جز مدیران کل) |
| `moderator` | فقط مشاهده سرویس‌های مرتبط با مشتری خود، کد ویجت و گزارش استفاده |

---

## 🌐 مسیرهای مهم (API و صفحات)

| مسیر | توضیح |
|---|---|
| `/mylumina/login` | صفحه ورود |
| `/mylumina/dashboard` | داشبورد |
| `/mylumina/admin/customers` | مدیریت مشتریان |
| `/mylumina/admin/users` | مدیریت کاربران |
| `/mylumina/admin/services` | مدیریت سرویس‌ها |
| `/mylumina/admin/subscription-plans` | مدیریت طرح‌های اشتراک |
| `/mylumina/moderator/services` | سرویس‌های من (ناظر) |
| `/mylumina/moderator/widget-code` | دریافت کد ویجت |
| `/mylumina/moderator/usage-report` | گزارش استفاده |
| `/mylumina/widget-inline` | ویجت به صورت iFrame |
| `/mylumina/api/widgets/chat-stream` | API استریم چت (برای ویجت) |

---

## 🧩 نحوه‌ی نصب ویجت روی سایت

پس از ایجاد یک سرویس در پنل، به بخش **کد ویجت** بروید و کد تولید شده را در انتهای تگ `<body>` سایت خود قرار دهید:

```html
<!-- لومینا - ویجت چت هوشمند -->
<script src="https://your-domain.com/mylumina/widgets/chat-widget-loader.js"></script>
<script>
    window.ChatWidgetConfig = {
        serviceCode: 'svc_xxxxx',
        primaryColor: '#667eea',
        position: 'bottom-right',
        title: 'پشتیبانی آنلاین',
        welcomeMessage: 'سلام! چطور می‌تونم کمک کنم؟',
        autoOpen: false
    };
</script>
```

---

## 📊 گزارش‌گیری

- **داشبورد**: نمایش آمار کلی، نمودار چت‌های هفتگی و آخرین فعالیت‌ها
- **گزارش استفاده (ناظر)**: مشاهده لیست کاربران با تعداد چت‌ها و امکان خروجی اکسل کامل
- **خروجی اکسل**: شامل تمام چت‌های هر کاربر به همراه جزئیات کامل

---

## 🤝 مشارکت (Contributing)

۱. مخزن را **Fork** کنید  
۲. یک **Branch** جدید بسازید (`git checkout -b feature/amazing-feature`)  
۳. تغییرات خود را **Commit** کنید (`git commit -m 'Add some amazing feature'`)  
۴. **Push** کنید (`git push origin feature/amazing-feature`)  
۵. یک **Pull Request** باز کنید  

---

## 📜 لایسنس

این پروژه تحت لایسنس **MIT** منتشر شده است.  
برای اطلاعات بیشتر فایل `LICENSE` را ببینید.

---

## 📞 پشتیبانی

- **ایمیل**: support@your-domain.com  
- **وب‌سایت**: https://your-domain.com  
- **مخزن گیت‌هاب**: https://github.com/your-username/lumina

---

## 🙏 تقدیر و تشکر

- **OpenAI** برای فراهم کردن Assistants API
- **FontAwesome** برای آیکون‌ها
- **Chart.js** برای نمودارها
- **Vazirmatn** برای فونت فارسی

---

> ✨ **لومینا** - ساده، قدرتمند، هوشمند