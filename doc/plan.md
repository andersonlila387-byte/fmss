# FMSS — File & Information Security Management System

> **Vision:** A platform that secures files, source code, documents, and sensitive information using blockchain-inspired cryptography, and lets users share that information with each other through a verifiably secure channel — wrapped in a cinematic, high-motion interface.

---

## 1. Project Overview

FMSS is a web-based vault, password manager, and secure-sharing platform. Users upload files and store login credentials; the system encrypts them, fingerprints them cryptographically, and records every action in a tamper-evident ledger. When a user shares something, the recipient can prove the file is authentic and unaltered before opening it.

**Core promise to the user:**
- Nothing leaves your control unencrypted.
- Every file has a verifiable fingerprint — you can prove it was never tampered with.
- Every action (upload, share, download, delete) is permanently recorded in a chained, tamper-evident log.

---

## 2. Goals & Non-Goals

### Goals
- Encrypt files at rest (AES-256) and in transit (HTTPS/TLS).
- Generate a cryptographic fingerprint (SHA-256) for every file to detect tampering.
- Maintain a **hash-chained audit ledger** — the "blockchain" part — where each entry locks in the one before it.
- Allow secure, permissioned sharing between registered users.
- Deliver a polished, cinematic UI using motion design.

### Non-Goals (for v1)
- A fully decentralized, distributed blockchain network (no mining, no consensus across nodes). We use blockchain *techniques* (hashing, chaining, signatures) in a centralized app — this is realistic, fast, and secure.
- Cryptocurrency / tokens.
- Mobile native apps (web-responsive only in v1).

---

## 3. Tech Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| Markup / Structure | HTML5 | Semantic markup |
| Styling | Tailwind CSS | Utility-first, fast theming |
| Client logic | JavaScript (ES6+) | Vanilla JS |
| **Animation** | **Motion (motion.dev)** | ⚠️ See note below |
| Backend | PHP 8.x | Routing, crypto, business logic |
| Database | MySQL / MariaDB | Users, files, shares, ledger |
| Crypto | PHP `sodium` + `openssl` extensions | AES-256, SHA-256, signatures |
| Server | Apache / Nginx + PHP-FPM | HTTPS mandatory |

> ### ⚠️ Important: Framer Motion vs. Motion
> **Framer Motion is a React-only library.** It cannot run on a vanilla HTML/CSS/JS + PHP stack. To get the *same* cinematic animation feel without React, use **Motion** (https://motion.dev) — it's built by the same author, works in plain JavaScript, and gives you spring physics, scroll-linked animation, and timeline sequencing. Alternatives: **GSAP** (most powerful for cinematic sequences) or **AOS** (simple scroll reveals).
>
> Recommendation for FMSS: **Motion** for component/page transitions + **GSAP** for hero/intro cinematic sequences.

---

## 4. Security Model (The Core)

This is the heart of FMSS. Three layers work together.

### 4.1 Confidentiality — Client-Side End-to-End Encryption (CHOSEN: zero-knowledge)
**Decision (Q1): files are encrypted in the browser, before they ever reach the server.** The server only ever stores ciphertext. This means even FMSS staff / a server breach cannot read user files — true zero-knowledge.

- Files are encrypted **in the browser** with **AES-256-GCM** (Web Crypto API) before upload.
- Each file gets its own random **data key**.
- The data key is encrypted ("wrapped") with the user's **master key**, which is derived **in the browser** from their password using **Argon2id / PBKDF2**.
- The raw master key and password **never leave the device**. The server stores only: ciphertext, the wrapped data key, and the public key.
- Trade-off accepted: server-side preview/search on file *contents* isn't possible; previews and search run client-side after decryption, or on encrypted metadata. (See feature catalog for client-side search.)
- **Account recovery** uses a one-time **recovery kit** (downloadable recovery code) generated at signup — because if the password is lost, zero-knowledge means the data is unrecoverable without it.

### 4.2 Integrity — Fingerprinting
- On upload, compute the **SHA-256 hash** of the original file = its unique fingerprint.
- Store the fingerprint. On every download or share, recompute and compare.
- If the hashes differ → the file was tampered with → block and alert.

### 4.3 Tamper-Evidence — The Hash-Chained Ledger (the "blockchain")
Every meaningful action becomes a **block** in a chain:

```
Block N contains:
  - action        (UPLOAD | SHARE | DOWNLOAD | DELETE | LOGIN)
  - actor_id      (which user)
  - target_hash   (fingerprint of the file involved)
  - timestamp
  - previous_hash (SHA-256 of the entire previous block)
  - this_hash     (SHA-256 of all fields above)
```

Because each block embeds the hash of the previous one, **you cannot alter any past record without breaking every block after it.** A periodic "verify chain" job recomputes the whole chain and flags any break. This gives you a provable, immutable audit trail — the genuine blockchain principle, applied practically.

### 4.4 Authenticity — Digital Signatures (sharing)
- Each user has a keypair (Ed25519 via libsodium).
- When User A shares a file with User B, A **signs** the file's fingerprint.
- User B can verify the signature → proof it really came from A and wasn't swapped in transit.

---

## 5. System Architecture

```
┌─────────────────────────────────────────────┐
│                  BROWSER                      │
│  HTML + Tailwind + JS + Motion (animations)   │
│  - Encrypt/decrypt helpers (optional E2E)     │
└───────────────────┬───────────────────────────┘
                    │  HTTPS (TLS)
┌───────────────────▼───────────────────────────┐
│              PHP APPLICATION                   │
│  ┌─────────┐ ┌──────────┐ ┌────────────────┐  │
│  │  Auth   │ │  Crypto  │ │ Ledger Service │  │
│  │ Service │ │ Service  │ │ (hash chain)   │  │
│  └─────────┘ └──────────┘ └────────────────┘  │
│  ┌─────────┐ ┌──────────┐ ┌────────────────┐  │
│  │  File   │ │  Share   │ │  Audit / Verify│  │
│  │ Service │ │ Service  │ │  Service       │  │
│  └─────────┘ └──────────┘ └────────────────┘  │
└──────┬─────────────────────────┬───────────────┘
       │                         │
┌──────▼──────┐          ┌────────▼────────┐
│  MySQL DB   │          │ Encrypted File  │
│ (metadata,  │          │ Storage (disk / │
│  ledger)    │          │ object storage) │
└─────────────┘          └─────────────────┘
```

---

## 6. Database Schema (initial)

**users**
- id (PK), username, email, telegram_chat_id, telegram_verified, password_hash (Argon2id), public_key, encrypted_private_key, created_at, status

**files**
- id (PK), owner_id (FK), original_name, stored_name, mime_type, size, file_hash (SHA-256), encrypted_data_key, iv, created_at, is_deleted

**logins**
- id (PK), owner_id (FK), title, encrypted_payload (JSON with username, password, url, notes), encrypted_data_key, iv, created_at, updated_at

**shares**
- id (PK), file_id (FK), sender_id (FK), recipient_id (FK), signature, permission (view/download), expires_at, status, created_at

**ledger** (the hash chain)
- id (PK), block_index, action, actor_id, target_hash, payload, previous_hash, this_hash, created_at

**sessions**
- id (PK), user_id (FK), token_hash, ip, user_agent, expires_at

---

## 7. Feature Breakdown

### Authentication
- Register, login, logout
- Argon2id password hashing
- Optional 2FA (TOTP) — phase 2
- Telegram Bot integration (collect chat_id on registration, send approval code, password recovery)
- Session management + CSRF protection

### File Vault
- Drag-and-drop upload (encrypted on receipt)
- File list with fingerprint badge
- Download (decrypt + integrity check)
- Soft delete + secure permanent delete
- File preview for safe types (images, PDF, text)

### Password & Credential Vault
- Store URLs, usernames, passwords, and secure notes
- Client-side encryption of all credential fields
- Built-in secure password generator
- One-click copy to clipboard with auto-clear

### Secure Sharing
- Share to another FMSS user by username/email
- Set permission (view-only / download)
- Set expiry date
- Signed transfer — recipient verifies authenticity
- Revoke a share at any time

### Audit & Verification
- Per-file activity timeline
- "Verify integrity" button (recompute hash)
- "Verify ledger" admin tool (recompute full chain)
- Tamper alerts

### Dashboard
- Storage used, file count, recent activity
- Security score / status widget

---

## 8. Project Folder Structure

```
FMSS/
├── plan.md
├── public/                  # web root
│   ├── index.php            # front controller
│   ├── assets/
│   │   ├── css/             # compiled Tailwind
│   │   ├── js/
│   │   │   ├── app.js
│   │   │   ├── animations.js  # Motion / GSAP sequences
│   │   │   └── crypto.js      # client-side helpers
│   │   └── img/
├── src/
│   ├── Controllers/
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── CryptoService.php
│   │   ├── FileService.php
│   │   ├── ShareService.php
│   │   └── LedgerService.php
│   ├── Models/
│   └── Core/                # Router, DB, Request, Response
├── config/
│   └── config.php
├── storage/
│   ├── encrypted/           # encrypted blobs (outside web root!)
│   └── logs/
├── database/
│   └── schema.sql
├── vendor/                  # composer deps
└── tailwind.config.js
```

> **Security note:** `storage/encrypted/` must live **outside the public web root** so files are never directly URL-accessible.

---

## 9. Pages / Screens

| Page | Purpose |
|------|---------|
| Landing | Cinematic hero, value proposition, CTA |
| Register / Login | Auth with animated transitions |
| Dashboard | Overview, stats, recent activity |
| My Vault | File grid/list, upload, search |
| Passwords | List of saved logins, add new, password generator |
| File Detail | Preview, fingerprint, activity timeline, share |
| Shared With Me | Files others sent you |
| Share Modal | Pick recipient, permission, expiry |
| Audit Log | Ledger view + verify button |
| Settings | Profile, password, keys, 2FA |

---

## 10. API Endpoints (PHP routes)

```
POST   /auth/register
POST   /auth/login
POST   /auth/logout
POST   /auth/verify-telegram # submit Telegram approval code
POST   /auth/forgot-password # request recovery OTP via Telegram
POST   /auth/reset-password  # submit Telegram OTP + new password

GET    /files                # list my files
POST   /files                # upload (encrypt + hash + ledger)
GET    /files/{id}           # metadata
GET    /files/{id}/download  # decrypt + integrity check
DELETE /files/{id}
GET    /files/{id}/verify    # recompute hash

GET    /logins               # list saved login credentials
POST   /logins               # save new encrypted login
PUT    /logins/{id}          # update existing login
DELETE /logins/{id}          # delete login

POST   /shares               # create signed share
GET    /shares/incoming
GET    /shares/outgoing
DELETE /shares/{id}          # revoke

GET    /ledger               # my activity
GET    /ledger/verify        # admin: verify full chain
```

---

## 11. Animation / UX Direction (Cinematic Motion)

Goal: make security feel *premium and trustworthy*, not clinical.

- **Library:** Motion (motion.dev) for app transitions + GSAP for the landing hero.
- **Landing hero:** staggered text reveal, parallax depth, a slowly rotating/forming "chain of blocks" visual that animates as you scroll.
- **Page transitions:** smooth fade + slide with spring physics (not linear).
- **File upload:** a "sealing" animation — file shrinks into a glowing encrypted block, fingerprint hash types out character-by-character.
- **Verification success:** a satisfying check/lock animation with a subtle particle or glow burst.
- **Micro-interactions:** buttons with spring hover, cards that lift on hover, loading states with shimmer.

**Motion principles:** ease-out for entrances, spring for interactions, keep durations 200–500ms (longer only for hero), respect `prefers-reduced-motion`.

Suggested palette direction: deep navy / near-black base, electric cyan or violet accents (security + tech feel), glassmorphism cards.

---

## 12. Development Roadmap

### Phase 1 — Foundation (Week 1–2)
- Project scaffold, router, DB connection, Tailwind build
- Schema + migrations
- Auth (register/login/logout, Argon2id, sessions, CSRF)

### Phase 2 — Core Vault (Week 3–4)
- CryptoService (AES-256-GCM, key wrapping)
- Upload → encrypt → hash → store
- File list, download (decrypt + verify), delete
- LedgerService (hash chain) wired into every action

### Phase 3 — Secure Sharing (Week 5)
- Ed25519 keypairs per user
- Signed shares, recipient verification
- Permissions + expiry + revoke

### Phase 4 — Audit & Verify (Week 6)
- Activity timelines
- Integrity verify + full ledger verify
- Tamper alerts

### Phase 5 — Polish & Motion (Week 7)
- Landing hero + cinematic animations
- Page transitions, micro-interactions
- Responsive pass, reduced-motion support

### Phase 6 — Hardening & Launch (Week 8)
- Security checklist (below), pen-test pass
- Rate limiting, input validation audit
- Deploy with HTTPS

---

## 13. Security Checklist

- [ ] HTTPS enforced everywhere (HSTS)
- [ ] Passwords hashed with Argon2id — never stored or logged in plain text
- [ ] AES-256-GCM for file encryption, unique key per file
- [ ] Encrypted storage kept **outside** the web root
- [ ] All DB access via prepared statements (no SQL injection)
- [ ] CSRF tokens on all state-changing requests
- [ ] Output escaping to prevent XSS
- [ ] File-type & size validation on upload (block executables/dangerous types)
- [ ] Rate limiting on auth + upload
- [ ] Secure, HttpOnly, SameSite session cookies
- [ ] Ledger chain verified on a schedule
- [ ] Secrets/keys in environment config, never committed to git

---

## 14. Future Enhancements (post-v1)

- True end-to-end encryption (encrypt in browser before upload)
- 2FA (TOTP / WebAuthn)
- File versioning with per-version fingerprints
- Group/team vaults with role-based access
- Optional anchoring of ledger checkpoints to a public blockchain for external proof
- Mobile apps
- Folder structure & tagging

---

## 15. Decisions Made (resolved)

1. **Encryption model → Client-side End-to-End (zero-knowledge).** Files encrypted in the browser before upload; server stores only ciphertext. Safest option. Requires a recovery-kit flow (no password = no data).
2. **Storage → S3-compatible object storage** for encrypted blobs (durable, scalable, safest against loss). Local disk only for dev.
3. **Storage limits → 10 GB free per user**, paid tiers for more (see Section 16).
4. **Sharing → Multi-recipient (up to 5 at once)**, plus secure link sharing and local WiFi/peer-to-peer sharing (see Section 17). In scope for v1.

---

## 16. Pricing & Plans

| Plan | Storage | Recipients / share | Max file size | Price | Highlights |
|------|---------|--------------------|---------------|-------|------------|
| **Free** | 10 GB | up to 5 | 2 GB | $0 | E2E encryption, link share, WiFi/P2P, tamper-evident ledger |
| **Pro** | 200 GB | up to 5 | 10 GB | paid | Versioning, custom links, priority transfer, no ads |
| **Business** | 1 TB+ | up to 5 | 25 GB | paid | Team vaults, RBAC, audit export, e-signature |
| **Enterprise** | Custom | custom | custom | custom | SSO/SAML, HIPAA mode, data residency, SCIM, SLA |

- Free users get **10 GB**; need more → upgrade. Storage add-on packs available on Pro+.
- Recipient cap is **5 per share** across all plans (per requirement); can be raised in future.
- **Referral bonus storage** and **promo codes** supported (see catalog).

---

## 17. Sharing Architecture (3 modes)

### Mode 1 — Direct user share (multi-recipient, up to 5)
- Sender picks up to **5 FMSS users**.
- For each recipient, the file's data key is **re-wrapped to that recipient's public key**, so only they can decrypt it (zero-knowledge preserved).
- Sender **signs** the file fingerprint with their Ed25519 key → recipient verifies authenticity.
- Ciphertext stays in cloud storage; access is permissioned, revocable, and logged in the ledger.

### Mode 2 — Secure link share
- Generates a link. The **decryption key lives in the URL fragment (`#...`)**, which browsers never send to the server — so the server still can't read the file.
- Options: expiry, password, one-time-use (burns after open), max downloads, email-gating, QR code.

### Mode 3 — Local WiFi / Peer-to-Peer ("access point" sharing)
- **Realistic implementation: WebRTC data channels** — the file goes **device-to-device over the same WiFi/LAN**, never touching the cloud. Channel is end-to-end encrypted (DTLS).
- Devices pair via a **QR code or short code** (lightweight signaling). The sender's device acts as the **host**; up to **5 nearby devices** can join.
- **Honest note on "turn my phone into a WiFi access point":** a web browser **cannot** make a phone act as a hotspot by itself — that's an OS-level action. Practical flow for v1: both devices join the same network (the sender can manually enable their phone's hotspot in OS settings if no shared WiFi), then pair in FMSS via QR/code and transfer P2P. A true built-in "device-as-server hotspot" experience needs a **native app wrapper (Capacitor/Flutter)** — flagged for **Phase 2**.

---

## 18. The 300+ Feature Master Catalog

> This is the **master backlog** — a complete checklist so nothing is missed. It is **not** all v1 scope. Build in the order of the roadmap (Section 12); treat features tagged conceptually as "core" (auth, encryption, vault, sharing, ledger) as MVP, and the rest as the long roadmap. Check items off as they ship.

### A. Authentication & Account
- [ ] F001 — Email + password registration
- [ ] F002 — Argon2id password hashing
- [ ] F003 — Email verification
- [ ] F004 — Login with rate limiting
- [ ] F005 — Logout / kill session
- [ ] F006 — 2FA via authenticator app (TOTP)
- [ ] F007 — 2FA via email OTP
- [ ] F008 — WebAuthn / passkey / biometric login
- [ ] F009 — "Remember this device"
- [ ] F010 — Recovery kit (downloadable code) generation
- [ ] F011 — Account recovery via recovery kit
- [ ] F012 — Password change with master-key re-encryption
- [ ] F013 — Social login (Google/GitHub) optional
- [ ] F014 — Login activity / device history
- [ ] F015 — Force-logout all devices
- [ ] F356 — Collect Telegram chat_id on registration
- [ ] F357 — Telegram bot approval/verification code flow
- [ ] F359 — Forgot password / account recovery OTP via Telegram bot

### B. Encryption & Cryptography
- [ ] F016 — Client-side AES-256-GCM file encryption
- [ ] F017 — Per-file random data keys
- [ ] F018 — Key wrapping with master key
- [ ] F019 — Argon2id/PBKDF2 key derivation in browser
- [ ] F020 — Ed25519 keypair per user
- [ ] F021 — Digital signing of shared files
- [ ] F022 — Signature verification on receipt
- [ ] F023 — SHA-256 file fingerprinting
- [ ] F024 — Encrypted file names / metadata
- [ ] F025 — Encrypted folder names
- [ ] F026 — Zero-knowledge architecture (server never sees plaintext)
- [ ] F027 — Key rotation support
- [ ] F028 — Hardware security key support
- [ ] F029 — Key escrow option (enterprise)
- [ ] F030 — Crypto-shredding (delete key = unrecoverable)

### C. File Vault & Storage
- [ ] F031 — Drag-and-drop upload
- [ ] F032 — Multi-file upload
- [ ] F033 — Folder upload
- [ ] F034 — Chunked/resumable upload for large files
- [ ] F035 — Upload progress with cinematic "sealing" animation
- [ ] F036 — S3-compatible encrypted storage
- [ ] F037 — Storage quota tracking (10 GB free)
- [ ] F038 — Storage usage breakdown by type
- [ ] F039 — Trash / recycle bin
- [ ] F040 — Restore from trash
- [ ] F041 — Permanent secure delete (crypto-shred)
- [ ] F042 — Auto-empty trash after N days
- [ ] F043 — Folder creation & nesting
- [ ] F044 — Move files between folders
- [ ] F045 — Star / favorite files

### D. File Operations
- [ ] F046 — Rename file
- [ ] F047 — Duplicate file
- [ ] F048 — Download (decrypt + integrity check)
- [ ] F049 — Bulk download as ZIP
- [ ] F050 — Bulk select & actions
- [ ] F051 — File versioning (keep history)
- [ ] F052 — Restore previous version
- [ ] F053 — Compare versions
- [ ] F054 — File locking (prevent edits)
- [ ] F055 — File expiry / auto-delete date
- [ ] F056 — Color labels
- [ ] F057 — Notes/description per file
- [ ] F058 — Pin file to top
- [ ] F059 — Sort by name/date/size/type
- [ ] F060 — Grid / list / compact view toggle

### E. Sharing & Collaboration
- [ ] F061 — Share with single FMSS user
- [ ] F062 — Multi-recipient share (up to 5)
- [ ] F063 — Permission levels (view / download / edit)
- [ ] F064 — Signed share (authenticity proof)
- [ ] F065 — Revoke share anytime
- [ ] F066 — Share expiry date
- [ ] F067 — Download limit per share
- [ ] F068 — Password-protected share
- [ ] F069 — Watermark shared previews
- [ ] F070 — View-only (disable download)
- [ ] F071 — Notify recipient on share
- [ ] F072 — See who viewed/downloaded
- [ ] F073 — Shared-with-me inbox
- [ ] F074 — Outgoing shares manager
- [ ] F075 — Re-share controls (allow/block)

### F. WiFi / Local / P2P Sharing
- [ ] F076 — Local peer-to-peer transfer (WebRTC)
- [ ] F077 — Device acts as local host
- [ ] F078 — QR-code pairing for nearby devices
- [ ] F079 — Nearby-device discovery on same network
- [ ] F080 — Direct transfer (no cloud upload)
- [ ] F081 — Offline LAN transfer mode
- [ ] F082 — Transfer speed / progress display
- [ ] F083 — Encrypted P2P channel (DTLS)
- [ ] F084 — Resume interrupted P2P transfer
- [ ] F085 — Group local share (one-to-many, up to 5)
- [ ] F086 — PIN confirmation before P2P accept
- [ ] F087 — Auto-expire local sessions

### G. Link Sharing & Access Control
- [ ] F088 — Generate secure share link
- [ ] F089 — One-time-use link (burns after open)
- [ ] F090 — Time-limited link
- [ ] F091 — Password on link
- [ ] F092 — QR code for any link
- [ ] F093 — Custom link alias
- [ ] F094 — Link click analytics
- [ ] F095 — Disable/revoke link
- [ ] F096 — Max-downloads on link
- [ ] F097 — Email-gated link
- [ ] F098 — Domain-restricted link
- [ ] F099 — Geo/IP-restricted link

### H. Audit, Ledger & Verification
- [ ] F100 — Hash-chained tamper-evident ledger
- [ ] F101 — Log every action (upload/share/download/delete/login)
- [ ] F102 — Per-file activity timeline
- [ ] F103 — Verify single file integrity
- [ ] F104 — Verify entire ledger chain
- [ ] F105 — Tamper alert notifications
- [ ] F106 — Exportable audit report (PDF/CSV)
- [ ] F107 — Immutable timestamps
- [ ] F108 — Ledger search & filter
- [ ] F109 — Visual block-explorer view
- [ ] F110 — Optional public-blockchain anchoring of checkpoints
- [ ] F111 — Chain-of-custody certificate per file

### I. Security & Threat Protection
- [ ] F112 — HTTPS/TLS enforced + HSTS
- [ ] F113 — CSRF protection
- [ ] F114 — XSS output escaping
- [ ] F115 — SQL injection prevention
- [ ] F116 — Rate limiting (auth/upload/share)
- [ ] F117 — Brute-force lockout
- [ ] F118 — Suspicious login detection
- [ ] F119 — Malware/virus scan on upload
- [ ] F120 — File-type allowlist / dangerous-type block
- [ ] F121 — Session timeout
- [ ] F122 — Auto-lock vault on inactivity
- [ ] F123 — Screenshot/print deterrents (view-only)
- [ ] F124 — IP allowlist / blocklist
- [ ] F125 — Decoy/canary files (intrusion alert)
- [ ] F126 — Security event webhook (SIEM)

### J. Privacy & Anonymity
- [ ] F127 — Zero-knowledge by default
- [ ] F128 — No-log download mode
- [ ] F129 — Anonymous share links
- [ ] F130 — Metadata stripping (EXIF) on upload
- [ ] F131 — Self-destruct files/messages
- [ ] F132 — Private/incognito vault section
- [ ] F133 — Hidden vault (PIN-gated)
- [ ] F134 — Duress password (opens decoy vault)
- [ ] F135 — Do-Not-Track honored
- [ ] F136 — Data residency selection
- [ ] F137 — Full account wipe (right to be forgotten)
- [ ] F138 — Encrypted activity history

### K. Search & Organization
- [ ] F139 — Client-side encrypted search index
- [ ] F140 — Search by filename/tag/note
- [ ] F141 — Search within document text (client-side)
- [ ] F142 — Saved searches / smart folders
- [ ] F143 — Filter by type/date/size/owner
- [ ] F144 — Recent files view
- [ ] F145 — Tag manager
- [ ] F146 — Bulk tagging
- [ ] F147 — Auto-categorization by type
- [ ] F148 — Folder templates
- [ ] F149 — Breadcrumb navigation
- [ ] F150 — Command palette (Ctrl/Cmd+K)

### L. File Preview & Viewer
- [ ] F151 — Image preview (post-decrypt)
- [ ] F152 — PDF viewer
- [ ] F153 — Text/markdown viewer
- [ ] F154 — Video player (streamed decrypt)
- [ ] F155 — Audio player
- [ ] F156 — Office docs preview
- [ ] F157 — 3D model preview (glb/obj)
- [ ] F158 — Client-side thumbnail generation
- [ ] F159 — Image gallery / lightbox
- [ ] F160 — Zoom/pan/rotate viewer
- [ ] F161 — Full-screen presentation mode
- [ ] F162 — Side-by-side file compare

### M. Code & Developer Features
- [ ] F163 — Syntax-highlighted code viewer
- [ ] F164 — Encrypted code-snippet vault
- [ ] F165 — Secret/API-key manager
- [ ] F166 — .env secure storage
- [ ] F167 — Code diff viewer
- [ ] F168 — Git repo secure backup
- [ ] F169 — Line numbers & wrap toggle
- [ ] F170 — Copy-to-clipboard with auto-clear
- [ ] F171 — Code share with expiry
- [ ] F172 — Language auto-detection
- [ ] F173 — Markdown README rendering
- [ ] F174 — Inline code commenting

### N. Document Management
- [ ] F175 — Document tagging & categories
- [ ] F176 — Document expiry reminders
- [ ] F177 — e-Signature on documents
- [ ] F178 — Signature request workflow
- [ ] F179 — Document templates
- [ ] F180 — OCR on scanned docs (client-side)
- [ ] F181 — Convert to PDF
- [ ] F182 — Merge/split PDFs
- [ ] F183 — Redaction tool
- [ ] F184 — Approval status / checklist

### O. Notifications & Alerts
- [ ] F185 — In-app notification center
- [ ] F186 — Email notifications
- [ ] F187 — Web push notifications
- [ ] F188 — Share-received alert
- [ ] F189 — Download/view alert
- [ ] F190 — Security alert (new login)
- [ ] F191 — Quota-nearly-full alert
- [ ] F192 — Expiry-approaching alert
- [ ] F193 — Per-type notification preferences
- [ ] F194 — Digest/summary emails
- [ ] F358 — Telegram bot notifications (user & admin)

### P. Dashboard & Analytics
- [ ] F195 — Overview dashboard
- [ ] F196 — Storage-used widget
- [ ] F197 — File type breakdown chart
- [ ] F198 — Recent activity feed
- [ ] F199 — Security score widget
- [ ] F200 — Share statistics
- [ ] F201 — Most-accessed files
- [ ] F202 — Login/location map
- [ ] F203 — Storage trend chart
- [ ] F204 — Animated stat counters
- [ ] F205 — Exportable analytics
- [ ] F206 — Customizable dashboard widgets

### Q. User Profile & Settings
- [ ] F207 — Profile (name, avatar, bio)
- [ ] F208 — Avatar upload
- [ ] F209 — Theme: dark/light/auto
- [ ] F210 — Accent color picker
- [ ] F211 — Animation intensity control
- [ ] F212 — Language selection
- [ ] F213 — Timezone & date format
- [ ] F214 — Default share settings
- [ ] F215 — Connected devices list
- [ ] F216 — Export all data (encrypted)
- [ ] F217 — Delete account
- [ ] F218 — Notification settings hub

### R. Subscription, Billing & Plans
- [ ] F219 — Free tier (10 GB)
- [ ] F220 — Pro / Business / Enterprise tiers
- [ ] F221 — Storage add-on packs
- [ ] F222 — Stripe/PayPal integration
- [ ] F223 — Upgrade/downgrade flow
- [ ] F224 — Billing history & invoices
- [ ] F225 — Promo/coupon codes
- [ ] F226 — Referral rewards (bonus storage)
- [ ] F227 — Usage metering display
- [ ] F228 — Auto-renew toggle
- [ ] F229 — Grace period on overage
- [ ] F230 — Team seat management

### S. Admin & Management
- [ ] F231 — Admin dashboard
- [ ] F232 — User management (suspend/ban)
- [ ] F233 — Role-based access control (RBAC)
- [ ] F234 — System-wide audit log
- [ ] F235 — Storage/usage monitoring
- [ ] F236 — Plan/quota management
- [ ] F237 — Broadcast announcements
- [ ] F238 — Feature flags / toggles
- [ ] F239 — Abuse/report queue
- [ ] F240 — Impersonate user (logged, support)
- [ ] F241 — Backup/restore controls
- [ ] F242 — Maintenance mode

### T. UI/UX & Cinematic Motion
- [ ] F243 — Cinematic landing hero (GSAP)
- [ ] F244 — Animated "chain of blocks" scroll visual
- [ ] F245 — Page transitions (Motion spring physics)
- [ ] F246 — File "sealing"/encrypt animation
- [ ] F247 — Fingerprint type-out animation
- [ ] F248 — Verification success burst
- [ ] F249 — Glassmorphism cards
- [ ] F250 — Spring hover micro-interactions
- [ ] F251 — Skeleton/shimmer loading states
- [ ] F252 — Animated empty states
- [ ] F253 — Parallax depth on scroll
- [ ] F254 — Confetti/particles on milestones
- [ ] F255 — Physics-based drag-and-drop
- [ ] F256 — Animated progress rings
- [ ] F257 — Reduced-motion mode

### U. Accessibility & Localization
- [ ] F258 — WCAG 2.1 AA compliance
- [ ] F259 — Full keyboard navigation
- [ ] F260 — Screen-reader ARIA labels
- [ ] F261 — High-contrast theme
- [ ] F262 — Adjustable font size
- [ ] F263 — Visible focus indicators
- [ ] F264 — Multi-language (i18n)
- [ ] F265 — RTL language support
- [ ] F266 — Localized dates/numbers/currency
- [ ] F267 — Captions for media

### V. Mobile & Cross-Device
- [ ] F268 — Fully responsive design
- [ ] F269 — PWA (installable)
- [ ] F270 — Offline mode (cached vault)
- [ ] F271 — Mobile camera upload
- [ ] F272 — Mobile biometric unlock
- [ ] F273 — Touch gestures (swipe actions)
- [ ] F274 — Cross-device sync
- [ ] F275 — QR-scan to open on another device
- [ ] F276 — Mobile share-sheet integration
- [ ] F277 — Adaptive per-screen layouts

### W. Integrations & API
- [ ] F278 — Public REST API
- [ ] F279 — API key management
- [ ] F280 — Webhooks
- [ ] F281 — Zapier/Make integration
- [ ] F282 — Slack/Discord notifications
- [ ] F283 — Google Drive / Dropbox import
- [ ] F284 — Cloud import (encrypt on arrival)
- [ ] F285 — SSO / SAML (enterprise)
- [ ] F286 — OAuth provider for third parties
- [ ] F287 — Developer docs & sandbox

### X. Backup & Recovery
- [ ] F288 — Automatic encrypted backups
- [ ] F289 — Scheduled backup config
- [ ] F290 — Point-in-time restore
- [ ] F291 — Recovery kit download/regenerate
- [ ] F292 — Trusted contacts (social recovery)
- [ ] F293 — Export encrypted archive
- [ ] F294 — Import from encrypted archive
- [ ] F295 — Multi-region redundancy
- [ ] F296 — Backup verification (hash check)
- [ ] F297 — Disaster-recovery failover

### Y. AI & Smart Features
- [ ] F298 — AI file auto-tagging (client-side)
- [ ] F299 — Smart duplicate detection
- [ ] F300 — Document summarization (on-device)
- [ ] F301 — Sensitive-data (PII/secrets) detection
- [ ] F302 — Smart search suggestions
- [ ] F303 — Anomaly detection (unusual access)
- [ ] F304 — Auto-organize suggestions
- [ ] F305 — AI-assisted file naming
- [ ] F306 — Content-based recommendations
- [ ] F307 — Phishing/malicious-link detection in shares
- [ ] F308 — Natural-language search
- [ ] F309 — OCR + AI text extraction

### Z. Compliance & Legal
- [ ] F310 — GDPR tooling
- [ ] F311 — CCPA compliance
- [ ] F312 — HIPAA-ready mode (enterprise)
- [ ] F313 — Data Processing Agreement (DPA)
- [ ] F314 — Consent management
- [ ] F315 — Audit-ready compliance reports
- [ ] F316 — Configurable retention policies
- [ ] F317 — Legal hold on files
- [ ] F318 — Terms/privacy acceptance tracking
- [ ] F319 — Breach-notification workflow

### AA. Performance & Reliability
- [ ] F320 — CDN for static assets
- [ ] F321 — Lazy loading & code splitting
- [ ] F322 — Service-worker caching
- [ ] F323 — Image optimization
- [ ] F324 — Uptime monitoring & status page
- [ ] F325 — Graceful error handling/fallbacks
- [ ] F326 — Auto-retry on failed transfers
- [ ] F327 — Background sync queue

### AB. Team / Enterprise
- [ ] F328 — Team/organization workspaces
- [ ] F329 — Shared team vaults
- [ ] F330 — Team roles & permissions
- [ ] F331 — Centralized billing
- [ ] F332 — Admin policy enforcement
- [ ] F333 — Team activity dashboard
- [ ] F334 — Department/group folders
- [ ] F335 — Guest/external collaborator access
- [ ] F336 — Provisioning (SCIM)
- [ ] F337 — Enterprise audit export

### AC. Gamification & Engagement
- [ ] F338 — Onboarding tour/checklist
- [ ] F339 — Security score gamification
- [ ] F340 — Achievement badges
- [ ] F341 — Streaks (e.g., backup streak)
- [ ] F342 — Referral leaderboard
- [ ] F343 — Tips/tooltips system
- [ ] F344 — "What's new" changelog modal
- [ ] F345 — Milestone rewards

### AD. Wow / Differentiator Features
- [ ] F346 — Self-destruct timed files
- [ ] F347 — Dead-man's switch (release if inactive)
- [ ] F348 — Inheritance / legacy contact (digital will)
- [ ] F349 — Stealth/duress decoy vault
- [ ] F350 — Printable tamper-proof certificate of authenticity
- [ ] F351 — Live collaborative encrypted notes
- [ ] F352 — Encrypted voice/video note attachments
- [ ] F353 — Cinematic "vault health" security report
- [ ] F354 — Personal blockchain-style block explorer
- [ ] F355 — Animated 3D vault visualization of your files

### AE. Password & Credential Manager
- [ ] F360 — Save login credentials (URL, username, password)
- [ ] F361 — Encrypt login payloads client-side
- [ ] F362 — Secure password generator tool
- [ ] F363 — Password strength indicator
- [ ] F364 — Copy-to-clipboard with 30-second auto-clear
- [ ] F365 — Secure notes field in logins
- [ ] F366 — Categorize logins (Personal, Work, Finance)

---

### Feature count: **366 features** (target: 300+) across 31 categories.

> **Build advice:** Don't build all 355 at once. **MVP = categories A, B, C, D, E, H** plus core UI from T (roughly F001–F075, F100–F111). Everything else is the long-term roadmap that makes FMSS "mind-blowing" over time. Use the checkboxes to track progress.

---

*FMSS — secure by design, verifiable by proof.*