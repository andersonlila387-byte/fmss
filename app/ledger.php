<?php
require_once __DIR__ . '/../src/Core/SessionManager.php';

// Protect this route: user must be logged in
$user = \FMSS\Core\SessionManager::requireAuthentication();

// ---------------------------------------------------------------------------
// Display values
// ---------------------------------------------------------------------------
$username = $user['username'] ?? 'User';
$email    = $user['email']    ?? '';
$status   = strtolower($user['status'] ?? 'active');
$tgVerified = !empty($user['telegram_verified']);

$parts    = preg_split('/[\s._\-]+/', trim($username));
$first    = $parts[0] ?? 'U';
$second   = $parts[1] ?? '';
$initials = strtoupper(substr($first, 0, 1) . ($second !== '' ? substr($second, 0, 1) : substr($first, 1, 1)));
$initials = $initials !== '' ? $initials : 'U';

$memberSince = '';
if (!empty($user['created_at'])) {
    $ts = strtotime((string) $user['created_at']);
    if ($ts) { $memberSince = date('M Y', $ts); }
}

$statusStyles = [
    'active'    => ['label' => 'Active',    'class' => 'text-green-400 bg-green-500/10 border-green-500/20'],
    'pending'   => ['label' => 'Pending',   'class' => 'text-amber-400 bg-amber-500/10 border-amber-500/20'],
    'suspended' => ['label' => 'Suspended', 'class' => 'text-red-400 bg-red-500/10 border-red-500/20'],
];
$statusMeta = $statusStyles[$status] ?? $statusStyles['active'];

// Mock Ledger Chain Data (descending order)
$mockBlocks = [
    ['index' => 4, 'action' => 'SECURE_SHARE', 'target' => 'Project_Zeus_Source.zip', 'meta' => 'To: ext_auditor', 'hash' => '8f2a...9b1c', 'prev' => '4c1d...3a2f', 'date' => '2 days ago', 'icon' => 'ph-share-network', 'color' => 'text-violet-400', 'bg' => 'bg-violet-500/10'],
    ['index' => 3, 'action' => 'CREDENTIAL_SAVE', 'target' => 'AWS Production', 'meta' => 'Encrypted payload', 'hash' => '4c1d...3a2f', 'prev' => '1a2b...3c4d', 'date' => '1 week ago', 'icon' => 'ph-key', 'color' => 'text-green-400', 'bg' => 'bg-green-500/10'],
    ['index' => 2, 'action' => 'FILE_UPLOAD', 'target' => 'Project_Zeus_Source.zip', 'meta' => 'AES-256-GCM', 'hash' => '1a2b...3c4d', 'prev' => 'e5f6...g7h8', 'date' => '2 weeks ago', 'icon' => 'ph-upload-simple', 'color' => 'text-blue-400', 'bg' => 'bg-blue-500/10'],
    ['index' => 1, 'action' => 'FILE_UPLOAD', 'target' => 'Server_Access_Keys.pem', 'meta' => 'AES-256-GCM', 'hash' => 'e5f6...g7h8', 'prev' => '0000...a1b2', 'date' => '1 month ago', 'icon' => 'ph-upload-simple', 'color' => 'text-blue-400', 'bg' => 'bg-blue-500/10'],
    ['index' => 0, 'action' => 'VAULT_INIT', 'target' => 'Genesis Block', 'meta' => 'Ledger spawned', 'hash' => '0000...a1b2', 'prev' => '0000...0000', 'date' => '1 month ago', 'icon' => 'ph-cube', 'color' => 'text-zinc-400', 'bg' => 'bg-zinc-500/10'],
];

// ---------------------------------------------------------------------------
// Metrics
// ---------------------------------------------------------------------------
$metrics = [
    'files'        => 4,
    'credentials'  => 5,
    'secureNotes'  => 0,
    'shares'       => 1,
    'ledgerBlocks' => count($mockBlocks),
    'storageUsed'  => 0.5,  // GB
    'storageTotal' => 10,   // GB
];
$storagePct = $metrics['storageTotal'] > 0
    ? round(($metrics['storageUsed'] / $metrics['storageTotal']) * 100, 1) : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Audit Ledger | FMSS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: {
                fontFamily: { sans: ['Outfit','sans-serif'], mono: ['Space Grotesk','monospace'] },
                colors: { zinc: { 950: '#09090b' } }
            }}
        }
    </script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        body { background:#000; color:#fff; -webkit-tap-highlight-color:transparent; }
        .glass-card {
            background: rgba(15,15,15,0.6);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.05);
        }
        ::-webkit-scrollbar { width:0; background:transparent; }
        .menu-panel { transform-origin: top right; transition: opacity .18s ease, transform .18s cubic-bezier(0.22,1,0.36,1); }
        .menu-panel[data-open="false"] { opacity:0; transform:scale(.96) translateY(-6px); pointer-events:none; }
        .menu-panel[data-open="true"]  { opacity:1; transform:scale(1) translateY(0); pointer-events:auto; }
        .grid-texture {
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 44px 44px;
        }
        .bar-fill { transition: width 1.1s cubic-bezier(0.22,1,0.36,1); }
        .chain-line { background: linear-gradient(to bottom, rgba(34,197,94,0.4) 0%, rgba(34,197,94,0.1) 100%); }
        @media (prefers-reduced-motion: reduce) { *, .menu-panel, .bar-fill { transition:none!important; animation:none!important; } }
    </style>
</head>
<body class="antialiased overflow-hidden selection:bg-green-500/30 selection:text-green-50">

<div class="flex h-screen w-full relative">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <!-- ============================ MAIN ============================ -->
    <main class="flex-1 flex flex-col h-full relative z-10 grid-texture">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-green-500/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-blue-500/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>

        <?php require_once __DIR__ . '/includes/header.php'; ?>

        <!-- Scrollable content -->
        <div class="flex-1 overflow-y-auto p-5 md:p-8 pb-32 md:pb-8 relative">

            <!-- Title & CTA -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 animate-item">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-1 tracking-tight">Audit Ledger</h1>
                    <p class="text-xs font-mono text-zinc-400">Tamper-evident cryptographic hash chain of all vault activity.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white font-medium text-sm px-4 py-2.5 rounded-xl border border-white/10 transition-colors shadow-lg">
                        <i class="ph ph-download-simple text-lg"></i> Export CSV
                    </button>
                    <button id="verify-btn" onclick="verifyChain()" class="flex items-center gap-2 bg-green-500 hover:bg-green-400 text-black font-semibold text-sm px-4 py-2.5 rounded-xl transition-colors shadow-[0_8px_30px_rgba(34,197,94,0.25)]">
                        <i class="ph ph-shield-check text-lg" id="verify-icon"></i> <span id="verify-text">Verify Chain Integrity</span>
                    </button>
                </div>
            </div>

            <!-- Ledger Chain UI -->
            <div class="relative max-w-4xl mx-auto animate-item">
                <!-- Connecting Line -->
                <div class="absolute left-[27px] md:left-[35px] top-6 bottom-6 w-[2px] chain-line rounded-full z-0"></div>

                <div class="space-y-6 relative z-10">
                    <?php foreach ($mockBlocks as $i => $block): ?>
                    <div class="flex gap-4 md:gap-6 group">
                        <!-- Timeline Node -->
                        <div class="relative shrink-0 flex flex-col items-center mt-2">
                            <div class="w-14 h-14 md:w-[72px] md:h-[72px] rounded-2xl bg-zinc-950 border-2 <?php echo $i === 0 ? 'border-green-500 shadow-[0_0_20px_rgba(34,197,94,0.3)]' : 'border-white/10 group-hover:border-green-500/50'; ?> flex flex-col items-center justify-center transition-colors relative z-10">
                                <span class="text-[10px] font-mono text-zinc-500 leading-none mb-1">#<?php echo $block['index']; ?></span>
                                <i class="ph <?php echo $block['icon']; ?> <?php echo $block['color']; ?> text-xl md:text-2xl"></i>
                            </div>
                        </div>

                        <!-- Block Details Card -->
                        <div class="flex-1 glass-card rounded-2xl p-4 md:p-5 border border-white/5 group-hover:bg-white/[0.03] transition-colors relative overflow-hidden">
                            <!-- Glow effect on latest block -->
                            <?php if ($i === 0): ?>
                            <div class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 blur-2xl rounded-full pointer-events-none"></div>
                            <?php endif; ?>

                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-mono font-bold text-white px-2 py-0.5 rounded bg-white/10 border border-white/10"><?php echo htmlspecialchars($block['action']); ?></span>
                                        <span class="text-xs text-zinc-500 font-mono"><i class="ph ph-clock text-zinc-600"></i> <?php echo $block['date']; ?></span>
                                    </div>
                                    <p class="text-sm font-medium text-white tracking-tight mt-2"><?php echo htmlspecialchars($block['target']); ?></p>
                                    <p class="text-xs text-zinc-500"><?php echo htmlspecialchars($block['meta']); ?></p>
                                </div>
                                
                                <button class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 text-zinc-400 hover:text-white transition-colors flex items-center justify-center shrink-0" title="View Raw Block">
                                    <i class="ph ph-code"></i>
                                </button>
                            </div>

                            <!-- Hashes -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 pt-4 border-t border-white/5">
                                <div class="bg-black/50 rounded-xl p-2.5 border border-white/5">
                                    <p class="text-[9px] font-mono text-zinc-600 uppercase tracking-widest mb-1">Previous Hash</p>
                                    <p class="text-[11px] font-mono text-zinc-400 truncate flex items-center gap-1.5"><i class="ph ph-link-break text-zinc-600"></i> <?php echo $block['prev']; ?></p>
                                </div>
                                <div class="bg-black/50 rounded-xl p-2.5 border border-green-500/10">
                                    <p class="text-[9px] font-mono text-green-500/70 uppercase tracking-widest mb-1">Block Hash</p>
                                    <p class="text-[11px] font-mono text-green-400 truncate flex items-center gap-1.5"><i class="ph ph-fingerprint text-green-500/50"></i> <?php echo $block['hash']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </main>

    <?php require_once __DIR__ . '/includes/bottom_nav.php'; ?>
</div>

<script>
    // Dropdowns & Date
    const menus = document.querySelectorAll('[data-menu]');
    function closeAll(except){ menus.forEach(m=>{ if(m===except)return; const p=m.querySelector('[data-menu-panel]'); if(p)p.setAttribute('data-open','false'); }); }
    menus.forEach(menu=>{ const t=menu.querySelector('[data-menu-trigger]'), p=menu.querySelector('[data-menu-panel]'); if(!t||!p)return; t.addEventListener('click',e=>{ e.stopPropagation(); const open=p.getAttribute('data-open')==='true'; closeAll(menu); p.setAttribute('data-open',open?'false':'true'); }); });
    document.addEventListener('click',()=>closeAll(null));
    document.addEventListener('keydown',e=>{ if(e.key==='Escape')closeAll(null); });
    const d=document.getElementById('liveDate'); function tick(){ if(!d)return; const n=new Date(); d.textContent=n.toLocaleDateString('en-US',{weekday:'long',month:'long',day:'numeric'})+' · '+n.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'}); } tick(); setInterval(tick,30000);

    // Verify Chain Interaction
    window.verifyChain = function() {
        const btn = document.getElementById('verify-btn');
        const icon = document.getElementById('verify-icon');
        const text = document.getElementById('verify-text');
        
        btn.disabled = true;
        btn.classList.add('opacity-80', 'cursor-not-allowed');
        icon.className = 'ph ph-spinner-gap animate-spin text-lg';
        text.innerText = 'Recomputing Hashes...';
        
        setTimeout(() => {
            btn.disabled = false;
            btn.classList.remove('opacity-80', 'cursor-not-allowed', 'bg-green-500', 'hover:bg-green-400');
            btn.classList.add('bg-white', 'text-black');
            icon.className = 'ph-fill ph-seal-check text-green-500 text-xl';
            text.innerHTML = 'Chain Verified <span class="font-mono text-zinc-500 text-xs ml-1">5 blocks</span>';
            
            setTimeout(() => {
                btn.classList.add('bg-green-500', 'hover:bg-green-400');
                btn.classList.remove('bg-white');
                icon.className = 'ph ph-shield-check text-lg';
                text.innerText = 'Verify Chain Integrity';
            }, 3000);
        }, 1500);
    }
</script>

<script type="module">
    import { animate, stagger } from "https://cdn.jsdelivr.net/npm/motion@11.11.13/+esm";
    if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches){ animate(".animate-item",{opacity:[0,1],y:[20,0]},{delay:stagger(0.1,{startDelay:0.1}),duration:0.6,easing:[0.22,1,0.36,1]}); }
</script>
</body>
</html>