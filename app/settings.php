<?php
require_once __DIR__ . '/../src/Core/SessionManager.php';
require_once __DIR__ . '/../src/Core/Database.php';

$user = \FMSS\Core\SessionManager::requireAuthentication();

$username = $user['username'] ?? 'User';
$email    = $user['email']    ?? '';
$status   = strtolower($user['status'] ?? 'active');

$parts    = preg_split('/[\s._\-]+/', trim($username));
$first    = $parts[0] ?? 'U';
$second   = $parts[1] ?? '';
$initials = strtoupper(substr($first, 0, 1) . ($second !== '' ? substr($second, 0, 1) : substr($first, 1, 1)));
$initials = $initials !== '' ? $initials : 'U';

// Handle PIN update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_pin'])) {
    $newPin = $_POST['new_pin'];
    if (strlen($newPin) === 4 && is_numeric($newPin)) {
        $db = \FMSS\Core\Database::getConnection();
        $hash = password_hash($newPin, PASSWORD_ARGON2ID);
        $stmt = $db->prepare("UPDATE users SET vault_pin_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        $message = "Vault PIN updated successfully.";
    } else {
        $message = "PIN must be exactly 4 digits.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Settings | FMSS</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['Outfit','sans-serif'], mono: ['Space Grotesk','monospace'] }, colors: { zinc: { 950: '#09090b' } } } } }
    </script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background:#000; color:#fff; -webkit-tap-highlight-color:transparent; }
        ::-webkit-scrollbar { width:0; background:transparent; }
        .glass-card { background: rgba(15,15,15,0.6); backdrop-filter: blur(16px); border: 1px solid rgba(34, 197, 94, 0.25); box-shadow: 0 8px 32px rgba(34, 197, 94, 0.08), inset 0 0 12px rgba(34, 197, 94, 0.02); }
        .grid-texture { background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 44px 44px; }
        .menu-panel { transform-origin: top right; transition: opacity .18s ease, transform .18s cubic-bezier(0.22,1,0.36,1); }
        .menu-panel[data-open="false"] { opacity:0; transform:scale(.96) translateY(-6px); pointer-events:none; }
        .menu-panel[data-open="true"]  { opacity:1; transform:scale(1) translateY(0); pointer-events:auto; }
    </style>
</head>
<body class="antialiased overflow-hidden selection:bg-green-500/30 selection:text-green-50">
    <div class="fixed inset-0 grid-texture z-0 pointer-events-none"></div>
    <div class="flex h-screen relative z-10">
        <?php require_once 'includes/sidebar.php'; ?>
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <?php require_once 'includes/header.php'; ?>
            <main class="flex-1 overflow-y-auto pb-24 md:pb-6 relative scroll-smooth">
                <div class="max-w-4xl mx-auto p-5 md:p-8 space-y-8">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold tracking-tight mb-1 text-white">Settings</h2>
                        <p class="text-sm text-zinc-400 font-light">Manage your vault security preferences and account details.</p>
                    </div>

                    <?php if ($message): ?>
                    <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Security Configuration -->
                        <div class="space-y-6">
                            <div class="glass-card rounded-3xl p-6">
                                <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2"><i class="ph ph-shield-check text-green-400"></i> Master Password</h3>
                                <p class="text-sm text-zinc-400 mb-6">Change the master password that encrypts your zero-knowledge vault.</p>
                                
                                <form onsubmit="event.preventDefault(); alert('Master Password change requires re-encryption of all vault keys. This feature is in development.');" class="space-y-4">
                                    <div>
                                        <input type="password" placeholder="Current Master Password" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-green-500/50 outline-none">
                                    </div>
                                    <div>
                                        <input type="password" placeholder="New Master Password" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-green-500/50 outline-none">
                                    </div>
                                    <button type="submit" class="w-full py-3 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition-colors">
                                        Update Password
                                    </button>
                                </form>
                            </div>

                            <div class="glass-card rounded-3xl p-6">
                                <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2"><i class="ph ph-numpad text-blue-400"></i> Vault PIN</h3>
                                <p class="text-sm text-zinc-400 mb-6">Set a 4-digit PIN for quick unlocks when returning to your active session.</p>
                                
                                <form method="POST" class="space-y-4">
                                    <div>
                                        <input type="password" name="new_pin" maxlength="4" pattern="\d{4}" required placeholder="••••" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-blue-500/50 outline-none font-mono tracking-[0.5em] text-center text-xl">
                                    </div>
                                    <button type="submit" class="w-full py-3 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition-colors">
                                        Save PIN
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Preferences & Export -->
                        <div class="space-y-6">
                            <div class="glass-card rounded-3xl p-6">
                                <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2"><i class="ph ph-bell-ringing text-amber-400"></i> Notifications</h3>
                                <p class="text-sm text-zinc-400 mb-6">Manage how FMSS contacts you via Telegram.</p>
                                
                                <div class="space-y-4">
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <div>
                                            <p class="text-sm font-medium text-white">Login Alerts</p>
                                            <p class="text-xs text-zinc-500">Get notified of new sign-ins.</p>
                                        </div>
                                        <div class="relative inline-block w-10 h-6">
                                            <input type="checkbox" class="peer sr-only" checked>
                                            <div class="w-10 h-6 bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                        </div>
                                    </label>
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <div>
                                            <p class="text-sm font-medium text-white">Vault Access Log</p>
                                            <p class="text-xs text-zinc-500">Alert me when the vault is decrypted.</p>
                                        </div>
                                        <div class="relative inline-block w-10 h-6">
                                            <input type="checkbox" class="peer sr-only">
                                            <div class="w-10 h-6 bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="glass-card rounded-3xl p-6">
                                <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2"><i class="ph ph-download-simple text-purple-400"></i> Export Vault</h3>
                                <p class="text-sm text-zinc-400 mb-6">Download a complete, encrypted backup of all your data (Logins, Cards, Notes, Files). This data can only be unlocked with your Master Password.</p>
                                
                                <button onclick="alert('Export functionality is pending final phase implementation.');" class="w-full py-3 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 font-medium rounded-xl transition-colors border border-purple-500/20 flex items-center justify-center gap-2">
                                    <i class="ph ph-archive"></i> Generate JSON Export
                                </button>
                            </div>
                            
                            <div class="glass-card rounded-3xl p-6 border-red-500/30">
                                <h3 class="text-lg font-semibold text-red-400 mb-2 flex items-center gap-2"><i class="ph ph-warning-circle"></i> Danger Zone</h3>
                                <p class="text-sm text-zinc-400 mb-6">Permanently delete your account and all encrypted data. This action is irreversible.</p>
                                
                                <button onclick="if(confirm('Are you absolutely sure? This cannot be undone.')) alert('Account deletion requires further verification.');" class="w-full py-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium rounded-xl transition-colors border border-red-500/20 flex items-center justify-center gap-2">
                                    <i class="ph ph-trash"></i> Delete Account
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
        <?php require_once 'includes/bottom_nav.php'; ?>
    </div>
    <script>
        // Date clock
        setInterval(() => {
            const el = document.getElementById('liveDate');
            if(el) el.innerText = new Date().toLocaleString('en-US', {weekday:'short', month:'short', day:'numeric', hour:'numeric', minute:'2-digit'});
        }, 1000);
        
        // Menu toggles
        document.querySelectorAll('[data-menu-trigger]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const panel = btn.nextElementSibling;
                if (!panel) return;
                const isOpen = panel.getAttribute('data-open') === 'true';
                document.querySelectorAll('[data-menu-panel]').forEach(p => p.setAttribute('data-open', 'false'));
                panel.setAttribute('data-open', !isOpen ? 'true' : 'false');
            });
        });
        document.addEventListener('click', () => {
            document.querySelectorAll('[data-menu-panel]').forEach(p => p.setAttribute('data-open', 'false'));
        });
    </script>
</body>
</html>
