# 📬 GH Timeline Mailer

A PHP-based email subscription system that verifies users via email and sends them GitHub timeline updates every 5 minutes using a CRON job.

---

## 🔗 Live Demo  
🌐 [http://gh-timeline.22web.org](http://gh-timeline.22web.org)

---

## ✨ Features
- Email verification with 6-digit OTP
- Stores verified emails in a `.txt` file (no database)
- Unsubscribe via verification code
- CRON job fetches and emails GitHub timeline
- Pure PHP (no external libraries)
- HTML email formatting

---

## 📂 Files Overview
- `index.php` – Form for email & code input  
- `functions.php` – All core logic functions  
- `unsubscribe.php` – Unsubscribe form & handler  
- `cron.php` – Scheduled email sender  
- `registered_emails.txt` – Stores subscriber emails  

---

## 📧 Email Formats

**Verification Email**
```html
<p>Your verification code is: <strong>123456</strong></p>
⚙️ CRON Setup
Runs cron.php every 5 minutes to:

Fetch GitHub events

Format as HTML

Email all verified users

🚀 How to Use
Visit the live site:

Submit your email

Enter the OTP received

Get GitHub updates via email

Unsubscribe anytime via email link

🛠 Tech Stack
PHP 8+

CRON Jobs (via hosting panel)

File-based storage

