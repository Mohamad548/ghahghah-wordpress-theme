# گزارش کامل پنل ادمین قالب قهقهه

تاریخ: ۲۰۲۶-۰۹-۱۸  
هدف: مرجع دیتا برای بازطراحی UI/UX پنل «پیکربندی قالب»  
منبع کد: `ghahghah-theme/inc/admin/`

---

## ۱) منوی اصلی وردپرس

| مورد | مقدار |
|------|--------|
| عنوان منو / صفحه | **پیکربندی قالب** |
| Capability | `edit_theme_options` |
| Slug | `ghahghah-theme-config` |
| آیکن | `dashicons-admin-customizer` |
| موقعیت | `59` |
| URL | `wp-admin/admin.php?page=ghahghah-theme-config&tab={slug}` |
| تب پیش‌فرض | `header` (هدر دسکتاپ) |

---

## ۲) اسکلت UI فعلی (Shell)

```
.wrap.ghahghah-config
├── header.ghahghah-config__hero
│   ├── eyebrow: «قالب قهقهه»
│   ├── عنوان: «پیکربندی قالب»
│   ├── توضیح کوتاه
│   └── chip تب فعال + لوگوی برند
├── toast موفقیت (اگر ?ghahghah_saved=1)
└── .ghahghah-config__layout
    ├── aside سایدبار (ناوبری)
    └── main پنل فعال
        ├── هدر پنل (عنوان + توضیح تب)
        └── بدنه → فایل panels/{tab}.php
```

### کلاس‌های کلیدی UI
- فرم: `ghahghah-panel-form`
- سکشن: `ghahghah-panel-section` / `__title` / `__desc`
- فیلد: `ghahghah-field` / `__label` / `__hint` / `__help`
- سوییچ: `ghahghah-switch`
- رسانه: `ghahghah-media-field`
- کارت لینک/وضعیت: `ghahghah-link-card` / `ghahghah-status-card`
- رپیترها: hero / steps / social / faq

### دارایی‌های ادمین
| فایل | نقش |
|------|-----|
| `assets/css/admin-config.css` | استایل پنل |
| `assets/js/admin-config.js` | تب، مدیا، رپیتر |
| `assets/css/fonts.css` | فونت یکان |

---

## ۳) نقشه سایدبار (۲۰ تب + ۱ گروه)

ترتیب فعلی ناوبری:

| # | نوع | شناسه | برچسب سایدبار | توضیح کوتاه | آیکن |
|---|-----|--------|----------------|--------------|------|
| 1 | آیتم | `header` | هدر دسکتاپ | لوگو، منو و دکمه بالای سایت | header |
| 2 | آیتم | `hero` | اسلایدر | بنرهای صفحه اصلی | hero |
| 3 | آیتم | `featured` | محصولات منتخب | کاروسل محصولات منتخب | featured |
| 4 | آیتم | `factory` | معرفی کارخانه | متن و تصویر بخش کارخانه | factory |
| 5 | آیتم | `steps` | مراحل تولید | مراحل تولید در صفحه اصلی | steps |
| 6 | آیتم | `collab` | خرید عمده و نمایندگی | کارت‌های همکاری در صفحه اصلی | collab |
| 7 | آیتم | `articles` | آخرین مطالب | کارت‌های نوشته در صفحه اصلی | articles |
| 8 | آیتم | `request-pages` | صفحات درخواست | برگه عمده و نمایندگی | collab |
| 9 | آیتم | `factory-page` | صفحه معرفی کارخانه | برگه و متن معرفی کارخانه | factory |
| 10 | آیتم | `faq` | پرسش‌های متداول | برگه و ویرایش پرسش‌ها | articles |
| 11 | آیتم | `contact-page` | صفحه تماس با ما | برگه و متن صفحه تماس | footer |
| 12 | آیتم | `archive-banners` | بنر آرشیوها | بنر مقالات و محصولات | hero |
| 13 | آیتم | `theme-media` | کتابخانه رسانه | همگام‌سازی تصاویر قالب | featured |
| 14 | آیتم | `seo` | سئو و متا | عنوان و توضیح صفحه اصلی | seo |
| 15 | **گروه** | `mobile` | موبایل | هدر، نوار پایین و فوتر موبایل | mobile |
| 15a | زیرمنو | `mobile-header` | هدر موبایل | لوگو و اندازه موبایل | mobile-header |
| 15b | زیرمنو | `mobile-bottom` | نوار پایین موبایل | ناوبری چسبان | mobile-bottom |
| 15c | زیرمنو | `mobile-footer` | فوتر موبایل | لوگوی فوتر موبایل | mobile-footer |
| 16 | آیتم | `footer` | فوتر دسکتاپ | ستون‌ها، تماس و متن حقوقی | footer |
| 17 | آیتم | `agency-requests` | درخواست نمایندگی | فرم و فهرست درخواست‌ها | agency |
| 18 | آیتم | `sms-settings` | پیامک | سامانه و الگوهای پیامک | sms |

> نکته: در متای تب‌ها، برچسب `agency-requests` برابر «درخواست‌ها» است؛ در سایدبار «درخواست نمایندگی».

### پیشنهاد گروه‌بندی برای UI جدید (اختیاری)

```
صفحه اصلی → header, hero, featured, factory, steps, collab, articles
صفحات داخلی → request-pages, factory-page, faq, contact-page, archive-banners
محتوا و رسانه → theme-media, seo
موبایل → mobile-*
فوتر و تماس سازمانی → footer
عملیات کسب‌وکار → agency-requests, sms-settings
```

---

## ۴) جزئیات هر پنل (سکشن‌ها، فیلدها، متن‌ها)

نوع فیلدها: `text` | `textarea` | `number` | `url` | `email` | `password` | `checkbox/toggle` | `pages` | `media` | `select` | `repeater` | `readonly`

ذخیره پیش‌فرض: `theme_mod` مگر خلاف آن ذکر شود.

---

### ۴.۱ هدر دسکتاپ (`header`)
دکمه ذخیره: **ذخیره تنظیمات هدر**  
Action: `ghahghah_save_header_settings`

#### سکشن: لوگو و آیکن دسکتاپ
توضیح: لوگوی دسکتاپ و فایوآیکن. تنظیمات موبایل در بخش «حالت موبایل» است.

| برچسب | نوع | کلید | پیش‌فرض / راهنما |
|--------|-----|------|-------------------|
| لوگوی دسکتاپ | media | `ghahghah_header_logo_desktop` | پیشنهاد: WebP شفاف، عرض حدود ۱۴۴px |
| فایوآیکن / آیکن سایت | media | `ghahghah_header_favicon` | PNG مربعی ۵۱۲×۵۱۲ |
| عرض لوگو دسکتاپ (پیکسل) | number ۸۰–۲۰۰ | `ghahghah_header_logo_width_desktop` | `144` |

#### سکشن: منوی اصلی
توضیح: عنوان، لینک، ترتیب و زیرمنو از فهرست‌های وردپرس.  
UI کمکی: کارت لینک → **باز کردن مدیریت فهرست‌ها**

#### سکشن: دکمه خرید عمده
| برچسب | نوع | کلید | پیش‌فرض |
|--------|-----|------|---------|
| نمایش دکمه در هدر | toggle | `ghahghah_header_cta_enabled` | روشن |
| متن دکمه | text (حداکثر ۴۰) | `ghahghah_header_cta_label` | درخواست خرید عمده |
| برگه مقصد | pages | `ghahghah_header_cta_page_id` | — انتخاب برگه — |

#### سکشن: رفتار هدر
| برچسب | نوع | کلید | پیش‌فرض |
|--------|-----|------|---------|
| هدر چسبان (Sticky) | toggle | `ghahghah_header_sticky` | روشن |

---

### ۴.۲ اسلایدر (`hero`)
دکمه: **ذخیره اسلایدر** · Action: `ghahghah_save_hero_settings`

#### سکشن: نمایش اسلایدر
| برچسب | نوع | کلید | پیش‌فرض |
|--------|-----|------|---------|
| نمایش اسلایدر در صفحه اصلی | checkbox | `ghahghah_hero_enabled` | روشن |
| مدت هر اسلاید (ثانیه) | number ۳–۲۰ | `ghahghah_hero_interval` | `6` |

#### سکشن: بنرها (Repeater — حداکثر ۱۲)
| برچسب | نوع | کلید آرایه |
|--------|-----|-----------|
| بنر دسکتاپ | media | `ghahghah_hero_slide_desktop[]` |
| بنر موبایل | media | `ghahghah_hero_slide_mobile[]` |
| لینک اکشن (اختیاری) | url | `ghahghah_hero_slide_link[]` |
| متن جایگزین تصویر (اختیاری) | text | `ghahghah_hero_slide_alt[]` |

UI ردیف: بالا / پایین / حذف · دکمه: **افزودن بنر**  
راهنما دسکتاپ: پیشنهاد ۱۹۱۶×۸۲۱ · موبایل: بنر مخصوص موبایل

---

### ۴.۳ محصولات منتخب (`featured`)
دکمه: **ذخیره محصولات منتخب** · Action: `ghahghah_save_featured_settings`

| برچسب | نوع | کلید | پیش‌فرض |
|--------|-----|------|---------|
| نمایش بخش در صفحه اصلی | checkbox | `ghahghah_featured_enabled` | روشن |
| عنوان بخش | text | `ghahghah_featured_title` | محصولات منتخب قهقهه |
| توضیح کوتاه | text | `ghahghah_featured_text` | طعم‌های قهقهه را بیشتر بشناسید. |
| متن دکمه «مشاهده همه» | text | `ghahghah_featured_all_label` | مشاهده همه محصولات |
| انتخاب محصول | checkbox per CPT | `ghahghah_featured_check[{id}]` → `ghahghah_featured_ids` | — |
| ترتیب | number | `ghahghah_featured_order[{id}]` | — |

راهنما: نام و تصویر از صفحه ویرایش محصول. حداکثر حدود ۱۲ آیتم.

---

### ۴.۴ معرفی کارخانه — استریپ صفحه اصلی (`factory`)
دکمه: **ذخیره تنظیمات** · Action: `ghahghah_save_factory_settings`

#### نمایش و متن
| برچسب | نوع | کلید | پیش‌فرض |
|--------|-----|------|---------|
| نمایش بخش معرفی کارخانه | checkbox | `ghahghah_factory_enabled` | روشن |
| عنوان کوتاه | text | `ghahghah_factory_eyebrow` | آشنایی با قهقهه |
| عنوان اصلی | textarea | `ghahghah_factory_title` | از نزدیک با کارخانه قهقهه آشنا شوید |
| توضیح | textarea | `ghahghah_factory_text` | در صفحه کارخانه، درباره مجموعه… |

#### تصویر کارخانه
| برچسب | نوع | کلید | راهنما |
|--------|-----|------|--------|
| تصویر کارخانه | media | `ghahghah_factory_image` | نسبت ≈۴:۳، ≥۹۶۰px |
| متن جایگزین تصویر (alt) | text | `ghahghah_factory_image_alt` | نمای داخلی کارخانه قهقهه |

#### سه موضوع
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| عنوان ردیف موضوعات | `ghahghah_factory_topics_heading` | تولید، بسته‌بندی و کنترل کیفیت |
| موضوع ۱ | `ghahghah_factory_topic_1` | مواد اولیه |
| موضوع ۲ | `ghahghah_factory_topic_2` | تولید و بسته‌بندی |
| موضوع ۳ | `ghahghah_factory_topic_3` | کنترل کیفیت |

#### دکمه
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| متن دکمه | `ghahghah_factory_button_label` | بیشتر درباره کارخانه |
| صفحه مقصد | `ghahghah_factory_button_page` (pages) | — |

---

### ۴.۵ مراحل تولید (`steps`)
دکمه: **ذخیره تنظیمات** · Action: `ghahghah_save_steps_settings`

| برچسب | نوع | کلید | پیش‌فرض / placeholder |
|--------|-----|------|------------------------|
| نمایش بخش مراحل تولید در صفحه اصلی | checkbox | `ghahghah_steps_enabled` | خاموش |
| عنوان کوتاه | text | `ghahghah_steps_eyebrow` | مثلاً: از مواد اولیه تا محصول |
| عنوان اصلی | text | `ghahghah_steps_title` | مثلاً: مراحل تولید محصول |
| توضیح | textarea | `ghahghah_steps_text` | جمله کوتاه زیر عنوان |

#### Repeater مراحل (حداکثر ۱۲)
| برچسب | کلید |
|--------|------|
| عنوان مرحله | `ghahghah_steps_title_item[]` |
| توضیح مرحله | `ghahghah_steps_text_item[]` |

UI: بالا/پایین/حذف · **افزودن مرحله**

---

### ۴.۶ خرید عمده و نمایندگی — صفحه اصلی (`collab`)
دکمه: **ذخیره تنظیمات** · Action: `ghahghah_save_collab_settings`

| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| نمایش بخش… | `ghahghah_collab_enabled` | روشن |
| عنوان کوتاه | `ghahghah_collab_eyebrow` | ارتباط با قهقهه |
| عنوان اصلی | `ghahghah_collab_title` | خرید عمده و درخواست نمایندگی |
| توضیح | `ghahghah_collab_text` | مسیر موردنظر خود را انتخاب کنید. |
| پیش‌نمایش کارت‌ها تا آماده شدن فرم Core | `ghahghah_collab_preview_forms` | خاموش |

#### کارت خرید عمده
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| عنوان کارت | `ghahghah_collab_wholesale_title` | خرید عمده محصولات |
| توضیح کارت | `ghahghah_collab_wholesale_text` | برای استعلام شرایط… |
| متن دکمه | `ghahghah_collab_wholesale_button` | درخواست خرید عمده |

#### کارت نمایندگی
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| عنوان کارت | `ghahghah_collab_agency_title` | درخواست نمایندگی |
| توضیح کارت | `ghahghah_collab_agency_text` | برای بررسی شرایط… |
| متن دکمه | `ghahghah_collab_agency_button` | ثبت درخواست نمایندگی |

UI کمکی: کارت وضعیت readiness فرم‌های Core (readonly).

---

### ۴.۷ آخرین مطالب (`articles`)
دکمه: **ذخیره تنظیمات** · Action: `ghahghah_save_articles_settings`

| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| نمایش بخش آخرین مطالب | `ghahghah_articles_enabled` | روشن |
| عنوان کوتاه | `ghahghah_articles_eyebrow` | مطالب و دانستنی‌ها |
| عنوان اصلی | `ghahghah_articles_title` | آخرین مطالب قهقهه |
| متن دکمه همه مطالب | `ghahghah_articles_all_label` | همه مطالب |
| متن لینک ادامه مطلب | `ghahghah_articles_more_label` | ادامه مطلب |
| دسته مطالب | category dropdown `ghahghah_articles_category` | همه نوشته‌ها |

---

### ۴.۸ صفحات درخواست (`request-pages`)
دکمه: **ذخیره تنظیمات صفحات** · Action: `ghahghah_save_request_pages`

#### برگه‌ها و تصویر
| برچسب | کلید |
|--------|------|
| برگه خرید عمده | `ghahghah_wholesale_page_id` |
| برگه درخواست نمایندگی | `ghahghah_agency_page_id` |
| تصویر معرفی خرید عمده | `ghahghah_wholesale_image_id` |

#### متن صفحه خرید عمده
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| عنوان اصلی | `ghahghah_wholesale_intro_title` | درخواست خرید عمده |
| زیرعنوان | `ghahghah_wholesale_intro_text` | اطلاعات سفارش را ثبت کنید… |
| برچسب کنار تصویر | `ghahghah_wholesale_hero_badge` | طعم پیتزا |
| کپشن پایین ستون چپ | `ghahghah_wholesale_footer_motto` | قهقهه همراه کسب‌وکارهای موفق! |
| نوار اطلاع‌رسانی پیامک | `ghahghah_wholesale_sms_note` | درخواست در سیستم ثبت می‌شود… |
| نوار اطلاع‌رسانی قیمت | `ghahghah_wholesale_price_note` | نمایش قیمت پس از بررسی… |
| متن لینک محصولات (legacy) | `ghahghah_wholesale_products_label` | مشاهده محصولات |
| عنوان فرم | `ghahghah_wholesale_form_title` | اطلاعات درخواست خرید عمده |
| عنوان مراحل (legacy) | `ghahghah_wholesale_steps_title` | مراحل بررسی درخواست |
| عنوان کارت نمایندگی (legacy) | `ghahghah_wholesale_cross_title` | برای همکاری در پخش؟ |
| متن کارت نمایندگی (legacy) | `ghahghah_wholesale_cross_text` | فرم درخواست نمایندگی را تکمیل کنید. |
| دکمه نمایندگی (legacy) | `ghahghah_wholesale_cross_button` | درخواست نمایندگی |

#### متن صفحه نمایندگی
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| سوپرهد | `ghahghah_agency_intro_eyebrow` | طعم لبخند در کنار شما |
| عنوان اصلی | `ghahghah_agency_intro_title` | درخواست نمایندگی قهقهه |
| زیرعنوان | `ghahghah_agency_intro_text` | اطلاعات همکاری خود را ثبت کنید… |
| تگ‌لاین کنار تصویر | `ghahghah_agency_hero_tagline` | با قهقهه بازار را لذیذتر کنید! |
| عبارت پایین ستون چپ | `ghahghah_agency_footer_motto` | آغاز یک همکاری موفق |
| عنوان فرم | `ghahghah_agency_form_title` | اطلاعات متقاضی نمایندگی |
| عنوان مسیر بررسی (legacy) | `ghahghah_agency_steps_title` | مسیر بررسی همکاری |
| عنوان کارت عمده (legacy) | `ghahghah_agency_cross_title` | قصد خرید عمده دارید؟ |
| متن کارت عمده (legacy) | `ghahghah_agency_cross_text` | درخواست محصول را در فرم… |
| دکمه عمده (legacy) | `ghahghah_agency_cross_button` | خرید عمده |

---

### ۴.۹ صفحه معرفی کارخانه (`factory-page`)
دکمه: **ذخیره صفحه کارخانه** · Action: `ghahghah_save_factory_page`

| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| برگه | `ghahghah_factory_page_id` | — |
| تصویر کارخانه | `ghahghah_factory_page_hero_image_id` | — |
| نمایش برچسب «تصویر موقت کارخانه» | `ghahghah_factory_page_show_temp_badge` | روشن |
| عنوان اصلی | `ghahghah_factory_page_title` | از دانه ذرت تا لحظه‌های خوشمزه |
| زیرعنوان | `ghahghah_factory_page_lead` | نگاهی به مسیر تولید… |
| نام شرکت | `ghahghah_factory_page_company` | شرکت بین‌المللی پیام صنعت پارسا |
| متن معرفی | `ghahghah_factory_page_intro` | متن بلند پیش‌فرض |
| دکمه محصولات | `ghahghah_factory_page_cta_label` | آشنایی با محصولات |
| عنوان مراحل تولید | `ghahghah_factory_page_process_title` | مراحل تولید محصول |
| توضیح مراحل | `ghahghah_factory_page_process_text` | از انتخاب مواد اولیه… |
| عنوان کیفیت | `ghahghah_factory_page_quality_title` | کیفیت در هر مرحله |
| توضیح کیفیت | `ghahghah_factory_page_quality_text` | متعهد به ارائه… |
| عنوان مدارک | `ghahghah_factory_page_certs_title` | مدارک و گواهینامه‌ها |
| توضیح مدارک | `ghahghah_factory_page_certs_text` | پس از دریافت مدارک رسمی… |

---

### ۴.۱۰ پرسش‌های متداول (`faq`)
دکمه: **ذخیره پرسش‌های متداول** · Action: `ghahghah_save_faq`  
ذخیره گروه‌ها: **post meta** `_ghahghah_faq_groups` روی برگه FAQ

#### برگه‌ها
| برچسب | کلید |
|--------|------|
| برگه پرسش‌های متداول | `ghahghah_faq_page_id` |
| برگه تماس با ما | `ghahghah_contact_page_id` |

#### معرفی و کارت‌های کناری
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| عنوان معرفی | `ghahghah_faq_intro_title` | پرسش‌های متداول |
| توضیح معرفی | `ghahghah_faq_intro_text` | راهنمای محصولات… |
| عنوان کارت تماس | `ghahghah_faq_support_title` | پاسخ خود را پیدا نکردید؟ |
| متن کارت تماس | `ghahghah_faq_support_text` | از راه‌های ارتباطی… |
| دکمه تماس | `ghahghah_faq_support_button` | تماس با ما |
| عنوان دسترسی به فرم‌ها | `ghahghah_faq_forms_title` | دسترسی به فرم‌ها |
| برچسب لینک خرید عمده | `ghahghah_faq_forms_wholesale` | خرید عمده |
| برچسب لینک نمایندگی | `ghahghah_faq_forms_agency` | درخواست نمایندگی |

#### Repeater تو در تو: گروه → پرسش
| برچسب | کلید |
|--------|------|
| عنوان گروه | `ghahghah_faq_groups[{i}][title]` |
| سؤال | `…[items][{j}][question]` |
| پاسخ | `…[items][{j}][answer]` |
| باز اولیه | `…[initially_open]` |
| متن لینک داخل پاسخ | `…[link_label]` |
| مقصد لینک | select: بدون لینک / عمده / نمایندگی / آرشیو محصولات / تماس |

UI: افزودن پرسش به گروه · افزودن گروه جدید

---

### ۴.۱۱ صفحه تماس با ما (`contact-page`)
دکمه: **ذخیره تنظیمات تماس** · Action: `ghahghah_save_contact_page`  
نکته UI: تلفن/ایمیل/نشانی از **فوتر** خوانده می‌شود.

| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| برگه | `ghahghah_contact_page_id` | — |
| آدرس نقشه | `ghahghah_contact_map_url` | لینک گوگل‌مپ |
| متن لینک روی نقشه | `ghahghah_contact_map_text` | مشاهده در گوگل مپ |
| عنوان اصلی | `ghahghah_contact_page_title` | راه‌های ارتباط با قهقهه |
| زیرعنوان | `ghahghah_contact_page_lead` | برای همکاری، خرید عمده… |
| عنوان فرم | `ghahghah_contact_form_title` | ارسال پیام برای ما |
| توضیح فرم | `ghahghah_contact_form_text` | نظر، پیشنهاد یا درخواست… |
| عنوان اطلاعات تماس | `ghahghah_contact_info_title` | اطلاعات تماس |
| توضیح اطلاعات تماس | `ghahghah_contact_info_text` | از طریق راه‌های زیر… |
| ساعات پاسخگویی | `ghahghah_contact_hours` | شنبه تا پنجشنبه — ۹ تا ۱۷ |
| متن جایگزین کارت‌ها | `ghahghah_contact_card_placeholder` | اطلاعات رسمی از پیشخوان… |

---

### ۴.۱۲ بنر آرشیوها (`archive-banners`)
دکمه: **ذخیره بنرهای آرشیو** · Action: `ghahghah_save_archive_banners`

| سکشن | برچسب | کلید |
|-------|--------|------|
| مقالات | بنر دسکتاپ | `ghahghah_blog_archive_banner_desktop_id` |
| مقالات | بنر موبایل (اختیاری) | `ghahghah_blog_archive_banner_mobile_id` |
| محصولات | بنر دسکتاپ | `ghahghah_products_archive_banner_desktop_id` |
| محصولات | بنر موبایل (اختیاری) | `ghahghah_products_archive_banner_mobile_id` |

اگر خالی باشد → طرح/تصویر پیش‌فرض قالب.

---

### ۴.۱۳ کتابخانه رسانه (`theme-media`)
بدون فیلد ویرایشی.  
دکمه‌ها: **همگام‌سازی با کتابخانه رسانه** · مشاهده کتابخانه رسانه  
Action: `ghahghah_sync_theme_media`  
UI: نوتیف آمار همگام‌سازی پس از اجرا.

---

### ۴.۱۴ سئو و متا (`seo`)
دکمه: **ذخیره تنظیمات سئو** · Action: `ghahghah_save_seo`

| برچسب | نوع | کلید | پیش‌فرض / راهنما |
|--------|-----|------|-------------------|
| عنوان SEO (تگ title) | text ≤۷۰ | `ghahghah_seo_home_title` | قهقهه \| اسنک ذرت ترد با طعم‌های متنوع |
| توضیح متا | textarea ≤۱۸۰ | `ghahghah_seo_home_description` | قهقهه، تولیدکننده اسنک ذرت… |
| عنوان H1 صفحه اصلی | text ≤۹۰ | `ghahghah_seo_home_h1` | قهقهه؛ طعم شادی با اسنک ذرت |
| تصویر OG پیش‌فرض | media | `ghahghah_seo_og_image_id` | اختیاری |

سکشن اطلاع‌رسانی: پوشش خودکار بقیه صفحات + واگذاری به Yoast/Rank Math.

---

### ۴.۱۵ هدر موبایل (`mobile-header`)
دکمه: **ذخیره تنظیمات هدر موبایل**

| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| لوگوی موبایل | `ghahghah_header_logo_mobile` | — |
| عرض لوگو موبایل (پیکسل) | `ghahghah_header_logo_width_mobile` | `112` (۷۲–۱۴۰) |

---

### ۴.۱۶ نوار پایین موبایل (`mobile-bottom`)
دکمه: **ذخیره نوار پایین موبایل**

| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| نمایش نوار پایین در موبایل | `ghahghah_bottom_nav_enabled` | روشن |

UI کمکی: لینک به فهرست‌ها + لینک به Customizer بخش `ghahghah_bottom_nav` + کارت اعتبارسنجی آیتم‌های منو.

---

### ۴.۱۷ فوتر موبایل (`mobile-footer`)
دکمه: **ذخیره فوتر موبایل**

| برچسب | کلید |
|--------|------|
| لوگوی فوتر موبایل | `ghahghah_footer_logo_mobile` |

---

### ۴.۱۸ فوتر دسکتاپ (`footer`)
دکمه: **ذخیره فوتر** · Action: `ghahghah_save_footer_settings`

#### برند
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| لوگوی فوتر (دسکتاپ) | `ghahghah_footer_logo` | — |
| معرفی کوتاه برند | `ghahghah_footer_intro` | قهقهه؛ طعم شادی… |

#### عناوین ستون‌ها
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| عنوان ستون دسترسی سریع | `ghahghah_footer_col_quick_title` | دسترسی سریع |
| عنوان ستون همکاری | `ghahghah_footer_col_business_title` | همکاری |
| عنوان ستون تماس | `ghahghah_footer_col_contact_title` | راه‌های ارتباط |

لینک کمکی: مدیریت فهرست‌های پاورقی / همکاری فوتر / حقوقی

#### نوار همکاری
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| نمایش نوار همکاری | `ghahghah_footer_collab_enabled` | روشن |
| عنوان نوار | `ghahghah_footer_collab_title` | همکاری با قهقهه |
| توضیح کوتاه | `ghahghah_footer_collab_text` | برای خرید عمده یا… |
| متن دکمه قرمز | `ghahghah_footer_collab_primary_label` | خرید عمده |
| برگه مقصد دکمه قرمز | `ghahghah_footer_collab_primary_page` | — |
| متن دکمه خطی | `ghahghah_footer_collab_secondary_label` | درخواست نمایندگی |
| برگه مقصد دکمه خطی | `ghahghah_footer_collab_secondary_page` | — |

#### اطلاعات تماس
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| شماره تلفن‌ها (هر خط یک شماره) | `ghahghah_footer_phones` | 087-35155151 / 09188805055 |
| ایمیل | `ghahghah_footer_email` | خالی |
| نشانی | `ghahghah_footer_address` | کارخانه سنندج + دفتر مرکزی… |

#### شبکه‌های اجتماعی (Repeater)
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| عنوان (دسترس‌پذیری) | `ghahghah_footer_social_label[]` | تلگرام / واتساپ |
| لینک | `ghahghah_footer_social_url[]` | — |
| نوع شبکه | select | تلگرام / واتساپ / سفارشی |
| آیکن سفارشی | media `ghahghah_footer_social_icon[]` | — |

#### حقوقی
| برچسب | کلید | پیش‌فرض |
|--------|------|---------|
| متن حقوقی | `ghahghah_footer_legal_text` | تمام حقوق این وب‌سایت متعلق به قهقهه است |
| برگه حریم خصوصی | `ghahghah_footer_privacy_page_id` | — |
| نمایش دکمه بازگشت به بالا | `ghahghah_footer_back_to_top` | روشن |

---

### ۴.۱۹ درخواست‌ها (`agency-requests`)
**بدون فرم ذخیره.**  
UI: توضیح + دکمه «مشاهده فهرست درخواست‌ها» (اگر CPT `ghahghah_inquiry` از پلاگین Core فعال باشد) یا پیام فعال‌سازی Core.

---

### ۴.۲۰ پیامک (`sms-settings`)
دکمه: **ذخیره تنظیمات پیامک** · Action: `ghahghah_save_sms_settings`  
ذخیره: **option** `ghahghah_sms_settings` (نه theme_mod)

#### سامانه ملی‌پیامک
| برچسب | کلید |
|--------|------|
| فعال‌سازی ارسال پیامک پس از ثبت موفق | `sms_enabled` |
| نام کاربری پنل / API Key | `sms_username` |
| API Key (اختیاری) | `sms_api_key` |
| رمز عبور / API Secret | `sms_api_secret` (password) |
| خط ارسال (اختیاری) | `sms_line` |
| شماره ادمین پیش‌فرض | `admin_phone` |

#### پترن خرید عمده / پترن نمایندگی
برای هر کدام تقریباً: فعال‌سازی کاربر/ادمین، کد پترن، متن پیام، شماره ادمین.  
نمایندگی اضافه: `agency_activities` — گزینه‌های زمینه فعالیت (هر خط یک مورد).

---

## ۵) نگاشت ذخیره (Save → Tab)

| Action | تب |
|--------|-----|
| `ghahghah_save_header_settings` | header |
| `ghahghah_save_mobile_header_settings` | mobile-header |
| `ghahghah_save_mobile_bottom_settings` | mobile-bottom |
| `ghahghah_save_hero_settings` | hero |
| `ghahghah_save_featured_settings` | featured |
| `ghahghah_save_factory_settings` | factory |
| `ghahghah_save_steps_settings` | steps |
| `ghahghah_save_collab_settings` | collab |
| `ghahghah_save_articles_settings` | articles |
| `ghahghah_save_request_pages` | request-pages |
| `ghahghah_save_factory_page` | factory-page |
| `ghahghah_save_faq` | faq |
| `ghahghah_save_contact_page` | contact-page |
| `ghahghah_save_archive_banners` | archive-banners |
| `ghahghah_sync_theme_media` | theme-media |
| `ghahghah_save_seo` | seo |
| `ghahghah_save_footer_settings` | footer |
| `ghahghah_save_mobile_footer_settings` | mobile-footer |
| `ghahghah_save_sms_settings` | sms-settings |
| — | agency-requests (بدون save) |

---

## ۶) الگوهای فیلد برای طراحی UI جدید

| الگو | کجا استفاده می‌شود | نیاز UI |
|------|---------------------|---------|
| Toggle روشن/خاموش | هدر، فوتر، بخش‌های صفحه اصلی، پیامک | سوییچ واضح + توضیح |
| Media picker | لوگو، بنر، تصویر کارخانه، OG | پیش‌نمایش + انتخاب/حذف/بازگشت به پیش‌فرض |
| Pages dropdown | مقصد دکمه‌ها، برگه صفحات | انتخاب برگه وردپرس |
| Text / Textarea کوتاه | تقریباً همه تب‌ها | شمارنده کاراکتر برای SEO |
| Repeater ساده | hero، steps، social | add / reorder / delete |
| Repeater تو در تو | FAQ | گروه → آیتم |
| Product picker | featured | چک‌باکس + ترتیب + تصویر بندانگشتی |
| Readonly hub | agency-requests، status کارت‌ها | لینک به جای دیگر |
| Sync action | theme-media | دکمه عمل + نتیجه |
| Secret fields | SMS | password / mask |

---

## ۷) تداخل با Customizer

| بخش Customizer | همپوشانی با پنل |
|----------------|------------------|
| `ghahghah_header` | عرض لوگو، CTA، sticky (جزئی از header/mobile-header) |
| `ghahghah_bottom_nav` | همان toggle نوار پایین موبایل |

بقیه تنظیمات تقریباً فقط در همین صفحه Config هستند.

---

## ۸) متن‌های ثابت شِل صفحه

- Eyebrow: **قالب قهقهه**
- عنوان صفحه: **پیکربندی قالب**
- Intro: ظاهر صفحه اصلی، هدر، فوتر و موبایل را از منوی کناری انتخاب کنید…
- سایدبار برچسب: **بخش‌ها**
- Toast نمونه: «تغییرات … ذخیره شد.»

---

## ۹) فایل‌های مرجع کد

| مسیر | نقش |
|------|-----|
| `inc/admin/config.php` | منو، تب‌ها، ناوبری، رندر شِل |
| `inc/admin/panels/*.php` | UI هر تب |
| `inc/admin/save-*.php` | ذخیره |
| `inc/admin/media-field.php` | کامپوننت رسانه |
| `assets/css/admin-config.css` | ظاهر فعلی |
| `assets/js/admin-config.js` | رفتار UI |

---

این فایل را می‌توانید مستقیم به عنوان **spec دیتا** برای طراحی پنل جدید استفاده کنید: منوها، ترتیب، برچسب‌ها، انواع فیلد، متن‌های پیش‌فرض و الگوهای تعامل همه اینجاست.
