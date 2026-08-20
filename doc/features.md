# FMSS Features Roadmap

Below is the comprehensive list of features planned for the FMSS application. We will use this document to track our progress.

## 1. Authentication & Security
- [x] Email/password account registration
- [x] Secure login with password hashing (Using Argon2id)
- [ ] 4-digit Vault PIN for second unlock layer
- [x] Two-Factor Authentication (2FA) via Telegram
- [ ] Account lockout after 5 failed login attempts
- [ ] Account lock after inactivity
- [ ] Trust device management
- [x] Full session management

## 2. Vault Categories
- [x] **Password:** Save login information (username, password, website URL)
- [x] **Document / Files:** Upload PDF, Word, Excel, images, videos
- [ ] **Secure Note:** Private encrypted text or messages
- [ ] **Bank & Card:** Bank information, CVV, card details, etc.
- [ ] **Custom:** Allow users to create their own custom fields

## 3. Encryption & Data Security
- [x] AES-256 military-grade encryption on all data (Client-side)
- [x] Every file encrypted client-side before saving to the server
- [x] Zero-knowledge architecture
- [ ] Encrypted export of the entire vault

## 4. Sharing & Access Control
- [x] Share any item via a secure link
- [ ] Set expiration time for shares (e.g., 1 hour, 24 hours, 7 days)
- [ ] Optional permissions: Choose whether recipient can download or view-only
- [ ] Revoke any share access instantly

## 5. Full Panel Management & Settings
- [ ] Settings Page
- [ ] Change master password
- [ ] Change Vault PIN
- [ ] Enable and disable notifications
