# Git Branch Naming and Tagging Guide (Professional)

> هدف: يك مرجع جامع و عملي براي نام گذاري برنچ ها و تگ ها در GitHub تا بتوانيد بر اساس آن، به صورت استاندارد برنچ بسازيد، كاميت كنيد و description بنويسيد.

---

## 1) اصول كلي و استانداردهاي پايه

- **قابل خواندن و قابل جستجو**: نام برنچ و تگ بايد بدون ابهام باشد.
- **كوتاه اما معنادار**: از كلمات كليدي ثابت استفاده كنيد.
- **فايل سيستم سازگار**: فقط از حروف كوچك لاتين، عدد، `-` و `/` استفاده كنيد. از فاصله و كاراكترهاي خاص خودداري كنيد.
- **ثبات ساختاري**: همه تيم يك الگو را دنبال كنند.
- **قابل رديابي**: بهتر است كد شناسه تسك/ايشيو را در نام داشته باشيد (مثل `EZ-123`).
- **زبان**: پیام کامیت و توضیحات Pull Request **حتماً به زبان انگلیسی** نوشته شوند. (Commit messages and PR descriptions must be written in English.)

الگوي كلي پيشنهادي:

```
<type>/<scope>-<ticket>-<short-slug>
```

نمونه:

```
feature/booking-ez-123-add-payment-retry
fix/auth-ez-98-refresh-token-loop
chore/ci-ez-201-update-pipeline
```

---

## 2) انواع نام گذاري برنچ ها

### 2.1) برنچ هاي اصلي (Long-lived)

- **`master` يا `main`**: نسخه پايدار و آماده انتشار.
- **`develop`** (اختياري): تجميع فيچرها قبل از انتشار.
- **`release/*`**: آماده سازي نسخه انتشار.
- **`hotfix/*`**: رفع سريع باگ بحراني روي نسخه منتشر شده.

نمونه:

```
main
release/1.12.0
hotfix/1.12.1-auth-cookie
```

### 2.2) برنچ هاي كوتاه مدت (Short-lived)

#### الف) Feature Branch
براي توسعه قابليت جديد.

الگو:

```
feature/<scope>-<ticket>-<short-slug>
```

نمونه:

```
feature/checkout-ez-311-add-bank-redirect
```

#### ب) Bugfix Branch
براي رفع باگ هاي معمولي.

الگو:

```
fix/<scope>-<ticket>-<short-slug>
```

نمونه:

```
fix/order-ez-405-double-charge
```

#### ج) Hotfix Branch
براي باگ بحراني روي توليد.

الگو:

```
hotfix/<scope>-<ticket>-<short-slug>
```

نمونه:

```
hotfix/auth-ez-501-invalid-session
```

#### د) Refactor Branch
براي بازآرايي بدون تغيير رفتار.

الگو:

```
refactor/<scope>-<ticket>-<short-slug>
```

#### هـ) Chore / Maintenance
براي كارهاي زيرساختي يا نگهداري.

الگو:

```
chore/<scope>-<ticket>-<short-slug>
```

#### و) Docs
براي مستندات.

الگو:

```
docs/<scope>-<ticket>-<short-slug>
```

#### ز) Test
براي افزودن يا اصلاح تست.

الگو:

```
test/<scope>-<ticket>-<short-slug>
```

---

## 3) استاندارد اسكوپ (Scope)

اسكوپ نشان مي دهد تغيير در كدام بخش است. نمونه هاي رايج:

- `api`
- `web-service`
- `wp-admin`
- `wp-content`
- `checkout`
- `orders`
- `auth`
- `db`
- `infra`

مثال:

```
feature/web-service-ez-222-add-reservation-filter
```

---

## 4) استفاده از شناسه تسك/ايشيو

براي رديابي آسان، شناسه تسك را وارد كنيد:

```
feature/orders-ez-140-add-financial-summary
```

اگر سيستم شما Jira يا مشابه است:

```
fix/payments-ez-77-retry-on-timeout
```

---

## 5) نام گذاري تگ ها (Tags)

### 5.1) Semantic Versioning (پيشنهادي)

الگو:

```
v<MAJOR>.<MINOR>.<PATCH>
```

- **MAJOR**: تغييرات ناسازگار
- **MINOR**: قابليت جديد سازگار
- **PATCH**: رفع باگ سازگار

نمونه:

```
v1.0.0
v1.4.2
v2.0.0
```

### 5.2) Pre-release و Build Metadata

```
v1.4.0-rc.1
v1.4.0-beta.2
v1.4.0-alpha.3
v1.4.0+build.20260206
```

### 5.3) تگ براي Hotfix

```
v1.4.3
```

---

## 6) نام گذاري Release Branch و Tag

- برنچ انتشار:

```
release/1.4.0
```

- تگ انتشار:

```
v1.4.0
```

---

## 7) الگوهاي كاميت (براي استفاده همراه برنچ)

**پیام کامیت باید به زبان انگلیسی باشد.** (Commit message must be in English.)

استاندارد **Conventional Commits** پيشنهاد مي شود:

```
<type>(<scope>): <short message>
```

انواع اصلي:

- `feat` (قابليت جديد)
- `fix` (رفع باگ)
- `docs` (مستندات)
- `refactor` (بازآرايي)
- `test` (تست)
- `chore` (نگهداري)

نمونه:

```
feat(orders): add daily financial summary
fix(auth): prevent refresh token loop
```

---

## 8) استاندارد Description براي PR يا كاميت

**توضیحات PR و متن کامیت باید به زبان انگلیسی نوشته شوند.** (PR descriptions and commit messages must be in English.)

الگوي حرفه اي براي Description:

```
Summary:
- <چه چيزي تغيير كرد>

Why:
- <چرا اين تغيير لازم بود>

How:
- <به طور خلاصه چطور پياده شد>

Risk:
- <ريسك ها يا موارد حساس>

Test:
- <چطور تست شد>
```

نمونه:

```
Summary:
- Add retry flow for bank redirect failures

Why:
- Reduce payment drop-offs during bank callback

How:
- Track redirect attempts and retry up to 2 times

Risk:
- Minimal; affects checkout only

Test:
- Manual test in staging
```

---

## 9) نمونه هاي كامل (End-to-End)

### نمونه 1: فيچر جديد

- برنچ:

```
feature/checkout-ez-311-add-bank-redirect-retry
```

- كاميت:

```
feat(checkout): add bank redirect retry
```

- تگ (بعد از release):

```
v1.5.0
```

### نمونه 2: رفع باگ

- برنچ:

```
fix/orders-ez-405-prevent-double-charge
```

- كاميت:

```
fix(orders): prevent double charge
```

- تگ:

```
v1.5.1
```

---

## 10) قوانين نهايي براي هماهنگي تيمي

- هميشه از يك الگو استفاده كنيد.
- بدون شناسه تسك برنچ نسازيد (جز موارد اضطراري).
- تگ ها فقط براي انتشار و با Semantic Versioning باشند.
- پيش از merge، نام برنچ و كاميت بايد حرفه اي باشد.
- **کامیت و توضیحات PR فقط به زبان انگلیسی**: Commit messages and PR descriptions must be written in English.

---

## 11) چطور از اين داکیومنت براي درخواست استفاده كنيد

وقتي مي خواهيد از AI بخواهيد برنچ و كاميت بسازد، اطلاعات زير را بدهيد:

- نوع كار: `feature` / `fix` / `docs` / ...
- اسكوپ: مثلا `orders`
- شناسه تسك: مثلا `EZ-405`
- خلاصه تغيير: مثلا `prevent double charge`
- نوع نسخه يا تگ (اگر نياز است): مثلا `v1.5.1`

مثال درخواست به AI:

```
Please create branch:
Type: fix
Scope: orders
Ticket: EZ-405
Slug: prevent double charge
Commit: fix(orders): prevent double charge
Description: use standard template
```

---

**پايان**
