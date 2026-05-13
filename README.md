# Exhibitions API

## متطلبات التشغيل
- PHP 8.2+
- Composer
- MySQL

## خطوات التشغيل

### 1. استنساخ المشروع
```bash
git clone https://github.com/salam-khaity/Exhibitions.git
cd Exhibitions
```

### 2. تثبيت المكتبات
```bash
composer install
```

### 3. إعداد ملف البيئة
```bash
cp .env.example .env
php artisan key:generate
```

### 4. إعداد قاعدة البيانات
في ملف `.env` عدّل:
DB_DATABASE=exhibitions
DB_USERNAME=root
DB_PASSWORD=

ثم:
```bash
php artisan migrate
```

### 5. تشغيل المشروع
```bash
php artisan serve
```

---

## إعداد Postman

### 1. Import الـ Collection
- افتح Postman
- اضغط **Import**
- ارفع ملف `postman/Authentication.json`

### 2. Import الـ Environment
- اضغط **Import** مرة ثانية
- ارفع ملف `postman/Exhibitions.environment.json`

### 3. اختر الـ Environment
- من القائمة في أعلى يمين Postman اختر **Exhibitions**

### 4. الاستخدام
- أرسل **Login** أو أي **Register** أولاً
- سيتم حفظ التوكن تلقائياً
- جميع الـ requests الأخرى ستعمل تلقائياً

---

## الأدوار المتاحة
| الدور | الوصف |
|-------|-------|
| admin | مدير النظام |
| organizer | منظم المعارض |
| exhibitor | عارض |
| visitor | زائر |
