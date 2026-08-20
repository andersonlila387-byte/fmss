<?php
require_once __DIR__ . '/../src/Core/SessionManager.php';
require_once __DIR__ . '/../src/Core/Database.php';

$user = \FMSS\Core\SessionManager::requireAuthentication();

// Fetch header variables
$username = $user['username'] ?? 'User';
$email    = $user['email']    ?? '';
$status   = strtolower($user['status'] ?? 'active');

$parts    = preg_split('/[\s._\-]+/', trim($username));
$first    = $parts[0] ?? 'U';
$second   = $parts[1] ?? '';
$initials = strtoupper(substr($first, 0, 1) . ($second !== '' ? substr($second, 0, 1) : substr($first, 1, 1)));
$initials = $initials !== '' ? $initials : 'U';

$db = \FMSS\Core\Database::getConnection();

// Fetch shares with joined file details
$stmt = $db->prepare("
    SELECT s.*, f.original_name, f.size, f.mime_type 
    FROM shares s 
    JOIN files f ON s.file_id = f.id 
    WHERE s.sender_id = ? 
    ORDER BY s.created_at DESC
");
$stmt->execute([$user['id']]);
$userShares = $stmt->fetchAll();

// Determine base URL for sharing links dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$baseShareUrl = "$protocol://$host/FMSS/share.php?id=";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Shared Items | FMSS Vault</title>
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
                <div class="max-w-4xl mx-auto p-5 md:p-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                        <div>
                            <h2 class="text-2xl md:text-3xl font-bold tracking-tight mb-1 text-white">Shared Links</h2>
                            <p class="text-sm text-zinc-400 font-light">Manage access to files you have shared securely.</p>
                        </div>
                    </div>

                    <div class="glass-card rounded-3xl border-white/5 overflow-hidden">
                        <div class="p-5 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
                            <h3 class="text-white font-semibold tracking-tight">Active & Past Shares</h3>
                            <span class="text-[10px] font-mono text-zinc-500 bg-white/5 border border-white/10 px-2 py-0.5 rounded-full"><?php echo count($userShares); ?> links</span>
                        </div>

                        <div class="divide-y divide-white/5">
                            <?php if (empty($userShares)): ?>
                            <div class="p-10 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mb-4"><i class="ph ph-share-network text-3xl text-zinc-600"></i></div>
                                <h4 class="text-white font-medium mb-1 tracking-tight">No shared files</h4>
                                <p class="text-sm text-zinc-500 max-w-xs font-light">You haven't generated any secure share links for your files yet.</p>
                            </div>
                            <?php else: ?>
                                <?php foreach ($userShares as $share): 
                                    $isActive = $share['status'] === 'active' && strtotime($share['expires_at']) > time();
                                    $isExpired = strtotime($share['expires_at']) <= time() && $share['status'] !== 'revoked';
                                    $isRevoked = $share['status'] === 'revoked';
                                    
                                    $statusColor = $isActive ? 'text-green-400' : ($isRevoked ? 'text-red-400' : 'text-amber-400');
                                    $statusText = $isActive ? 'Active' : ($isRevoked ? 'Revoked' : 'Expired');
                                    $shareUrl = $baseShareUrl . $share['id'];
                                ?>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-5 hover:bg-white/5 transition-colors gap-4">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="w-11 h-11 rounded-xl bg-white/5 flex items-center justify-center shrink-0">
                                            <i class="ph ph-file text-xl text-zinc-400"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-white truncate mb-0.5"><?php echo htmlspecialchars($share['original_name']); ?></p>
                                            <div class="flex items-center gap-3 text-[11px] font-mono">
                                                <span class="<?php echo $statusColor; ?>"><?php echo $statusText; ?></span>
                                                <span class="text-zinc-600">•</span>
                                                <span class="text-zinc-500"><?php echo $share['downloads_count']; ?> / <?php echo $share['max_downloads']; ?> DLs</span>
                                                <span class="text-zinc-600">•</span>
                                                <span class="text-zinc-500">Exp: <?php echo date('M j, Y H:i', strtotime($share['expires_at'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                                        <?php if ($isActive): ?>
                                            <button onclick="copyShareLink('<?php echo $shareUrl; ?>', this)" class="px-3 py-1.5 text-xs font-medium bg-white/5 hover:bg-white/10 text-zinc-300 rounded-lg transition-colors flex items-center gap-1.5">
                                                <i class="ph ph-copy"></i> <span>Copy</span>
                                            </button>
                                            <button onclick="revokeShare('<?php echo $share['id']; ?>')" class="px-3 py-1.5 text-xs font-medium bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-400 rounded-lg transition-colors flex items-center gap-1.5">
                                                <i class="ph ph-prohibit"></i> Revoke
                                            </button>
                                        <?php else: ?>
                                            <button disabled class="px-3 py-1.5 text-xs font-medium bg-zinc-900 text-zinc-600 rounded-lg cursor-not-allowed">
                                                <i class="ph ph-lock-key"></i> Locked
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                const isOpen = panel.getAttribute('data-open') === 'true';
                document.querySelectorAll('[data-menu-panel]').forEach(p => p.setAttribute('data-open', 'false'));
                panel.setAttribute('data-open', !isOpen ? 'true' : 'false');
            });
        });
        document.addEventListener('click', () => {
            document.querySelectorAll('[data-menu-panel]').forEach(p => p.setAttribute('data-open', 'false'));
        });

        async function copyShareLink(link, btnElement) {
            try {
                await navigator.clipboard.writeText(link);
                const originalHtml = btnElement.innerHTML;
                btnElement.innerHTML = '<i class="ph ph-check text-green-400"></i> <span class="text-green-400">Copied!</span>';
                setTimeout(() => {
                    btnElement.innerHTML = originalHtml;
                }, 2000);
            } catch(e) {
                alert("Failed to copy link.");
            }
        }

        async function revokeShare(shareId) {
            if(!confirm("Are you sure you want to revoke this share link? It will become permanently inactive.")) return;
            try {
                const res = await fetch('api/share_revoke.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({share_id: shareId})
                });
                const data = await res.json();
                if(data.success) {
                    window.location.reload();
                } else {
                    alert(data.error);
                }
            } catch(e) {
                alert(e.message);
            }
        }
    </script>
</body>
</html>
