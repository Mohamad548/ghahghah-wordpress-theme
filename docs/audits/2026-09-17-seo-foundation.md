# SEO Foundation — 2026-09-17 (`:8898`)

**Branch:** `fix/seo-foundation`  
**Base:** `3181fe192e7ad425ec41182ac33d100cdf093ea7`  
**Worktree / env:** `E:/word pares/ghahghah-fix-perf-images` · `http://localhost:8898`  
**Preserved:** `:8888`, prior branches, uploads, Performance/slider work, untracked artifacts  

Lab results are not a production CWV guarantee.  
**NO_LCP موبایل** و **Wholesale TBT** همچنان unresolved هستند و در این کار رفع اعلام نمی‌شوند.

---

## مهم: چه چیزی با Push کد منتقل نمی‌شود

| مورد | در Git؟ | انتقال به دامنه/محیط دیگر |
|------|---------|---------------------------|
| تغییر قالب (`front-page.php`) و اسکریپت/گزارش | بله | با Pull/Deploy کد |
| افزونه **Yoast SEO 28.5** (`wordpress-seo`) | **خیر** (داخل `wp-content/plugins` کانتینر) | باید جدا نصب/فعال شود |
| تنظیمات Yoast و `blog_public` و metaهای `_yoast_wpseo_*` | **خیر** (دیتابیس) | باید با دستورالعمل زیر بازتولید شوند |
| بکاپ SQL محلی | **خیر** (gitignore) | فقط بازیابی دستی روی همین env |

---

## Verdict

| هدف | نتیجه |
|-----|--------|
| H1 صفحه اصلی | **Fixed** — یک `<h1 class="screen-reader-text">قهقهه</h1>` همیشه با سکشن‌ها؛ وابسته به اسلاید/محصول/core نیست |
| مالک SEO | **Yoast Free 28.5** تنها افزونه SEO فعال؛ قالب موتور موازی نساخت |
| meta / OG / Twitter / JSON-LD / sitemap | توسط Yoast |
| ایندکس محیط تست | `blog_public=0` → `noindex, nofollow` (عمدی) |
| Inquiry در sitemap | نیست (`ghahghah_inquiry` غیرعمومی) |
| Search | `noindex` |
| 404 | وضعیت 404 حفظ؛ بدون canonical جعلی به Home |

---

## ۱) تغییرات کد (قابل Commit)

| فایل | تغییر |
|------|--------|
| `ghahghah-theme/front-page.php` | H1 برند همیشه وقتی سکشن‌های صفحه اصلی رندر می‌شوند؛ fallback بصری `front-intro` فقط وقتی هیچ سکشنی نیست (بدون H1 تکراری) |
| `scripts/seo-foundation-capture.cjs` | ثبت Before/After سیگنال‌های SEO از HTML سرور |
| `.gitignore` | نادیده گرفتن `docs/audits/artifacts/seo-foundation/backups/` و `*.sql` |
| `docs/audits/2026-09-17-seo-foundation.md` | این گزارش |
| `docs/audits/artifacts/seo-foundation/**` | شواهد HTML/خلاصه/تصاویر بصری/اسکریپت بازتولید تنظیمات (بدون SQL) |

**عمداً تغییر نکرد:** نسخه قالب، media sync، اسلایدر، دارایی‌های Performance، `:8888`.

---

## ۲) تغییرات محیط `:8898` (دیتابیس / افزونه — خارج از Git)

### قبل از تغییر
- افزونه SEO فعال: **هیچ** (فقط `ghahghah-core`)
- `blog_public`: **1**
- بکاپ محلی: `docs/audits/artifacts/seo-foundation/backups/seo-foundation-pre.sql` (+ snapshots گزینه/افزونه) — **untracked**

### نصب
```bash
wp plugin install wordpress-seo --version=28.5 --activate
# منبع رسمی: https://downloads.wordpress.org/plugin/wordpress-seo.28.5.zip
```
نسخه فعال تأییدشده: **28.5**

### تنظیمات اعمال‌شده
اسکریپت بازتولید: `docs/audits/artifacts/seo-foundation/configure-yoast-8898.php`

خلاصه:
- سازمان: نام سایت **قهقهه**؛ لوگو attachment **157** (`ghahghah-logo-desktop.webp`)
- `site_icon` → **161** در صورت خالی بودن
- `blog_public=0` (جلوگیری از ایندکس localhost)
- بدون verify/ping موتورهای جستجو
- Sitemap XML فعال؛ CPT عمومی `ghahghah_product` در sitemap؛ `ghahghah_inquiry` noindex / بدون metabox عمومی
- عنوان/توضیح صفحات اصلی از متن واقعی صفحه (بدون قیمت/امتیاز/ادعای ساختگی)
- Search: `noindex-search=true`

### CPT
`ghahghah_product` عمومی است با آرشیو `products` — **WooCommerce نیست**. Yoast آن را به‌عنوان CPT جداگانه پوشش می‌دهد.

---

## ۳) H1 صفحه اصلی

**علت قبل:** `front-page.php` فقط در `elseif` وقتی همه سکشن‌ها خاموش‌اند H1 می‌گذاشت؛ با Hero/Featured/… و `have_posts()` روی صفحه استاتیک، H1 هرگز چاپ نمی‌شد (`h1: []`).

**بعد:** با سکشن‌های فعال، یک H1 با `get_bloginfo( 'name' )` و کلاس موجود `screen-reader-text` (همان الگوی آرشیوهای تصویرمحور قالب). محتوای ویرایشگر صفحه اصلی خالی بود؛ عنوان تکراری از content ایجاد نشد.

مقایسه بصری موبایل/دسکتاپ: ترکیب Hero بدون تغییر ظاهری محسوس؛ H1 برای ATF مخفی است. ۹ اسلاید متمایز و decode سالم: `docs/audits/artifacts/seo-foundation/visual/`.

---

## ۴) جدول Before / After (HTML پاسخ سرور)

روش: `scripts/seo-foundation-capture.cjs` · artifacts در `docs/audits/artifacts/seo-foundation/{before,after}/`

| صفحه | Status B→A | H1 B→A | Meta desc | Canonical* | Robots A | OG/Twitter/JSON-LD A |
|------|------------|--------|-----------|------------|----------|----------------------|
| Home | 200→200 | `[]`→`قهقهه` | null→بله | Core داشت / زیر noindex حذف Yoast | `noindex, nofollow` | بله · انواع WebPage, WebSite, Organization, … |
| Products | 200→200 | ثابت | null→بله | قبلًا غالباً نبود | noindex | بله · CollectionPage + Org |
| Product (پنیری) | 200→200 | بعد: عنوان محصول نمایشی | بعد: غالباً از محتوا/OG | — | noindex | بله |
| Articles | 200→200 | ثابت | null→بله | — | noindex | بله |
| Article | 200→200 | ثابت | null (Yoast از محتوا/OG) | قبل Core داشت | noindex | بله · Article + Person |
| Wholesale | 200→200 | ثابت | null→بله | قبل Core | noindex | بله |
| Agency | 200→200 | ثابت | null→بله | قبل Core | noindex | بله |
| Contact | 200→200 | ثابت | null→بله | قبل Core | noindex | بله |
| Search | 200→200 | ثابت | — | — | `noindex` (قبل هم noindex) | بله |
| 404 | 404→404 | ثابت | — | **بدون canonical به Home** | `noindex` | JSON-LD سایت/سازمان؛ بدون og:url جعلی Home |

\* **Canonical و محیط تست:** با `blog_public=0` خروجی After عمداً `noindex` است و در snapshotها `canonical` خالی گزارش شد. با روشن کردن موقت ایندکس (`blog_public=1`) تأیید شد canonical صحیح است (مثلاً Home → `/` و محصول → permalink خودش). این را **اشکال قالب ندانید** — سیاست noindex تست است. برای دامنه اصلی: `blog_public=1`.

**آرشیو صفحه ۲ محصولات:** `/products/page/2/` → **404** → canonical صفحه۲ = **N/A**.

**یادداشت Before/product:** در اولین capture، URL محصول اشتباه به آرشیو resolve شد؛ ردیف After با permalink صحیح ثبت شده است.

چند `og:image` یا چند بلوک JSON-LD معتبر خطا محسوب نشدند.

---

## ۵) آزمون افزونه خاموش

1. `wp plugin deactivate wordpress-seo`
2. Home: `<title>` وردپرس + `title-tag` قالب سالم؛ H1 `قهقهه` باقی؛ canonical هسته؛ **بدون** meta/OG/Schema یواست
3. قالب خروجی SEO تکراری برای جبران نساخت
4. `wp plugin activate wordpress-seo` + `blog_public=0` بازگردانی شد

---

## ۶) اعتبارسنجی

| چک | نتیجه |
|----|--------|
| `php -l front-page.php` | PASS |
| `php scripts/verify-structure.php` | PASS |
| PHPUnit | **7/7** OK |
| PHPCS `front-page.php` vs Base | پس از نرمال‌سازی LF: **بدون خطای جدید** (فقط بدهی EOL اولیه با Write ویندوز رفع شد) |
| Smoke کامل `smoke-wordpress.sh` | FAIL تشخیص نسخه (`got unknown`) به‌خاطر مسیر Node/`ROOT` روی این worktree — مربوط به SEO نیست |
| Smoke معادل HTTP | 200 برای صفحات کلیدی؛ 404 برای URL ناموجود؛ sitemap 200؛ Yoast در head؛ تنظیمات حفظ |
| Visual Home + ۹ اسلاید | mobile+desktop: distinct=9، allDecoded؛ H1 موجود |
| Lighthouse کامل | اجرا نشد (طبق محدوده) |

---

## ۷) بازتولید روی محیط مقصد (دامنه اصلی)

1. Deploy کد قالب از این شاخه (حداقل `front-page.php`).
2. نصب Yoast Free **همان نسخه یا سازگار** از wordpress.org؛ **فقط یک** افزونه SEO فعال باشد.
3. اجرای منطبق `configure-yoast-8898.php` با ID لوگو/آیکون واقعی همان محیط (IDهای 157/161 مخصوص `:8898` هستند).
4. تنظیم `blog_public=1` روی پروداکشن؛ عناوین/توضیحات را با محتوای واقعی همان دامنه بازبینی کنید.
5. Search Console / verify را فقط روی دامنه واقعی — **هرگز localhost را ثبت نکنید**.
6. Sitemap: `https://<domain>/sitemap_index.xml` — مطمئن شوید Inquiry/فرم‌ها عمومی نیستند.
7. Permalinkها و اتصال Yoast به `ghahghah_product` را یک‌بار بازبینی کنید.

---

## موارد باقی‌مانده

- Meta description بعضی singleها (محصول/مقاله) ممکن است خالی بماند تا از excerpt/محتوا پر شود — در صورت نیاز در UI یواست تکمیل کنید؛ ادعا نسازید.
- عنوان جستجو هنوز انگلیسی Yoast («You searched for») — اختیاری برای فاز بعد.
- Smoke اسکریپت کامل روی این worktree نیاز به رفع تشخیص مسیر/`@wordpress/env` دارد (خارج از محدوده SEO).
- NO_LCP موبایل / Wholesale TBT: ** unresolved **.

---

## Git / تحویل

شاخه: `fix/seo-foundation` از Base بالا؛ merge نشده.  
افزونه و DB commit نمی‌شوند.
