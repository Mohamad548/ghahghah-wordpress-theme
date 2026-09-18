# ورک‌فلو راه‌اندازی سایت واقعی قهقهه (هاست)

تاریخ: 2026-09-18  
نسخه قالب هدف: `0.9.91+`  
مخاطب: نصب تازه روی هاست (مثل نت‌افراز / DirectAdmin) وقتی لوکال (`:8888`) کامل است ولی لایو خالی/ناقص است.

---

## تشخیص کارشناس (چرا لوکال خوب است و هاست نه؟)

| مورد کاربر | علت واقعی |
|---|---|
| ۱. محصولی نیست | قالب/افزونه فقط CPT را ثبت می‌کنند؛ **واردات کاتالوگ جداست** |
| ۲. برگه‌ای نیست | برگه‌های تماس/کارخانه/عمده/… با اسکریپت `setup-*.php` ساخته می‌شوند |
| ۳. منو نیست | جایگاه منو ثبت می‌شود؛ **فهرست ساخته نمی‌شود** مگر راه‌اندازی اولیه |
| ۴. عکس کارخانه نیست | `ghahghah_factory_image` قبلاً فقط از Media Library می‌آمد و sync پر نمی‌کرد |
| ۵. فوتر ناقص | لینک‌های برگه/منوی فوتر خالی بودند (بعد از bootstrap درست می‌شود) |
| ۶. عکس تکراری | همگام‌سازی رسانه موقع فعال‌سازی قالب (یا چندبار آپلود) بدون متا → کپی‌های اضافه |
| خطای بحرانی موقع فعال‌سازی قالب | sync سنگین همزمان با `after_switch_theme` → timeout/memory |

**نتیجه:** نصب ZIP قالب ≠ کپی‌کردن دیتای لوکال. روی هاست باید یک‌بار «راه‌اندازی اولیه» اجرا شود.

---

## پیش‌نیاز

1. PHP **8.1+** (با هاست فعلی سازگار است)
2. پوشه‌ها روی سرور:
   - `wp-content/themes/ghahghah-theme`
   - `wp-content/plugins/ghahghah-core`
3. افزونه **Ghahghah Core** فعال
4. قالب **قهقهه** فعال
5. قالب نسخه **0.9.91+** آپلود شده باشد (دکمه راه‌اندازی دارد)

---

## مسیر پیشنهادی روی هاست (بدون SSH)

1. وارد شوید: **پیکربندی قالب → کتابخانه رسانه**
2. دکمه **«اجرای راه‌اندازی اولیه»** را بزنید و صبر کنید (ممکن است ۳۰–۹۰ ثانیه طول بکشد)
3. نتیجه روی همان صفحه لیست می‌شود: media / pages / products / menus / cleanup
4. سایت را hard-refresh کنید
5. در صورت نیاز: **نمایش → فهرست‌ها** را یک‌بار باز کنید و ذخیره بزنید

### اگر دکمه را نمی‌بینید

قالب قدیمی است. پوشه `ghahghah-theme` را با نسخه جدید از Git جایگزین کنید، سپس دوباره فعال کنید.

---

## مسیر جایگزین با WP-CLI

```bash
wp plugin activate ghahghah-core
wp theme activate ghahghah-theme
wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/bootstrap-site.php
wp rewrite flush --hard
```

اسکریپت‌های جزئی (در صورت نیاز):

```bash
wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/import-product-catalog.php
wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-contact-page.php
wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-factory-page.php
wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-request-pages.php
wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-faq-page.php
wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-privacy-page.php
```

---

## ترتیب وابستگی داخل bootstrap

1. **رسانه** — تصاویر bundled → Media Library + theme_modها  
2. **برگه‌ها** — contact / factory / wholesale / agency / FAQ / privacy  
3. **محصولات** — ۹ محصول از `products.json`  
4. **منوها** — primary / footer / footer_business / legal / mobile_bottom  
5. **پاکسازی** — فایل‌های تکراری بدون متای قالب → زباله‌دان  

---

## بعد از راه‌اندازی چه چیزی درست می‌شود؟

- محصولات در منوی **محصولات**
- برگه‌های اختصاصی قالب
- هشدار «منوی اصلی تنظیم نشده» از بین می‌رود
- تصویر بخش کارخانه در صفحه اصلی
- بنر مراحل تولید + بنرهای عمده/نمایندگی (در صورت وجود فایل bundled)
- فوتر با لینک‌های واقعی
- کاهش عکس‌های تکراری بی‌متا

---

## کارهای دستی باقی‌مانده (اختیاری)

- تنظیم لینک‌های شبکه‌های اجتماعی فوتر
- تنظیم SMS در پنل پیامک
- انتشار مقالات استارتر:  
  `wp eval-file .../starter-articles/import-starter-articles.php`
- فعال‌سازی SSL / HTTPS در DirectAdmin
- به‌روزرسانی WordPress هسته

---

## رفع خطای بحرانی موقع فعال‌سازی

از نسخه `0.9.91` همگام‌سازی رسانه **به تعویق** می‌افتد و دیگر صفحه فعال‌سازی را از کار نمی‌اندازد.  
اگر هنوز خطای بحرانی دیدید:

1. در `wp-config.php` موقتاً بگذارید:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```
2. لاگ `wp-content/debug.log` را بخوانید
3. قالب را از File Manager غیرفعال کنید (پوشه را موقتاً rename کنید) تا ادمین برگردد

---

## نکته درباره عکس‌های تکراری موجود

دکمه راه‌اندازی، تکراری‌های **بدون متای قالب** را به زباله‌دان می‌فرستد (نسخه دارای `_ghahghah_theme_asset` نگه داشته می‌شود).  
از **رسانه → زباله‌دان** می‌توانید برای همیشه حذف کنید.
