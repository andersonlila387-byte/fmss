<?php
require_once __DIR__ . '/../src/Core/SessionManager.php';

// Protect this route: user must be logged in
$user = \FMSS\Core\SessionManager::requireAuthentication();

// ---------------------------------------------------------------------------
// Display values (defensive — not every field is guaranteed to be hydrated)
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

// ---------------------------------------------------------------------------
// Metrics — Fetch real counts
// ---------------------------------------------------------------------------
require_once __DIR__ . '/includes/metrics.php';

// ---------------------------------------------------------------------------
// Security Center checks (the "Watchtower")
// ---------------------------------------------------------------------------
$checks = [
    ['label' => 'Zero-knowledge encryption', 'ok' => true,                       'detail' => 'AES-256-GCM, client-side',  'action' => null,                                'href' => '#'],
    ['label' => 'Ledger integrity',          'ok' => true,                       'detail' => 'Chain verified',            'action' => null,                                'href' => '#'],
    ['label' => 'Weak passwords',            'ok' => $metrics['weak'] === 0,     'detail' => $metrics['weak'] . ' found', 'action' => $metrics['weak'] ? 'Review' : null,  'href' => '#'],
    ['label' => 'Reused passwords',          'ok' => $metrics['reused'] === 0,   'detail' => $metrics['reused'] . ' found', 'action' => $metrics['reused'] ? 'Review' : null, 'href' => '#'],
    ['label' => 'Data-breach exposure',      'ok' => $metrics['breached'] === 0, 'detail' => $metrics['breached'] . ' exposed', 'action' => $metrics['breached'] ? 'Inspect' : null, 'href' => '#'],
    ['label' => 'Telegram verification',     'ok' => $tgVerified,                'detail' => $tgVerified ? 'Verified' : 'Not verified', 'action' => $tgVerified ? null : 'Verify', 'href' => '#'],
    ['label' => 'Recovery kit',              'ok' => false,                      'detail' => 'Not downloaded',            'action' => 'Download',                          'href' => '#'],
];
$okCount   = count(array_filter($checks, fn ($c) => $c['ok']));
$checkTot  = count($checks);
$score     = $checkTot > 0 ? (int) round(($okCount / $checkTot) * 100) : 0;
$issues    = $checkTot - $okCount;
$scoreTone = $score >= 80 ? 'green' : ($score >= 50 ? 'amber' : 'red');
$scoreHex  = $scoreTone === 'green' ? '#22c55e' : ($scoreTone === 'amber' ? '#f59e0b' : '#ef4444');

// Vault category tiles
$categories = [
    ['label' => 'Logins',       'icon' => 'ph-key',           'count' => $metrics['credentials'], 'tint' => 'text-green-400 bg-green-500/10'],
    ['label' => 'Files',        'icon' => 'ph-folder',        'count' => $metrics['files'],       'tint' => 'text-blue-400 bg-blue-500/10'],
    ['label' => 'Secure Notes', 'icon' => 'ph-note',          'count' => $metrics['secureNotes'], 'tint' => 'text-amber-400 bg-amber-500/10'],
    ['label' => 'Shares',       'icon' => 'ph-share-network', 'count' => $metrics['shares'],      'tint' => 'text-violet-400 bg-violet-500/10'],
];

// Onboarding checklist
$checklist = [
    ['label' => 'Account created',        'done' => true],
    ['label' => 'Verify Telegram',        'done' => $tgVerified],
    ['label' => 'Upload your first file', 'done' => $metrics['files'] > 0],
    ['label' => 'Save a credential',      'done' => $metrics['credentials'] > 0],
    ['label' => 'Download recovery kit',  'done' => false],
];
$clDone = count(array_filter($checklist, fn ($c) => $c['done']));
$clTot  = count($checklist);
$clPct  = $clTot > 0 ? round(($clDone / $clTot) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard | FMSS Vault</title>

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
            border: 1px solid rgba(34, 197, 94, 0.25);
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.08), inset 0 0 12px rgba(34, 197, 94, 0.02);
        }
        ::-webkit-scrollbar { width:0; background:transparent; }
        .ring-progress { transition: stroke-dashoffset 1.6s cubic-bezier(0.22,1,0.36,1); }
        .menu-panel { transform-origin: top right; transition: opacity .18s ease, transform .18s cubic-bezier(0.22,1,0.36,1); }
        .menu-panel[data-open="false"] { opacity:0; transform:scale(.96) translateY(-6px); pointer-events:none; }
        .menu-panel[data-open="true"]  { opacity:1; transform:scale(1) translateY(0); pointer-events:auto; }
        .grid-texture {
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 44px 44px;
        }
        .row-actions { opacity:0; transition: opacity .2s ease; }
        .data-row:hover .row-actions { opacity:1; }
        .bar-fill { transition: width 1.1s cubic-bezier(0.22,1,0.36,1); }
        @media (prefers-reduced-motion: reduce) { *, .ring-progress, .menu-panel, .bar-fill { transition:none!important; animation:none!important; } }
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
        <div class="flex-1 overflow-y-auto p-5 md:p-8 pb-32 md:pb-8">

            <!-- Greeting + primary CTA -->
            <div class="flex items-start justify-between gap-4 mb-7 animate-item">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-1 tracking-tight"><span id="greeting">Welcome back</span>, <?php echo htmlspecialchars($username); ?></h1>
                    <div class="flex items-center gap-2 text-xs font-mono text-zinc-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                        Connection Secure &middot; E2E Active
                    </div>
                </div>
                <button class="hidden md:flex items-center gap-2 bg-green-500 hover:bg-green-400 text-black font-semibold text-sm px-4 py-2.5 rounded-xl transition-colors shadow-[0_8px_30px_rgba(34,197,94,0.25)]">
                    <i class="ph ph-plus-circle text-lg"></i> New item
                </button>
            </div>

            <!-- KPI cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <?php
                $kpis = [
                    ['Files','ph-folder',$metrics['files']],
                    ['Credentials','ph-key',$metrics['credentials']],
                    ['Active Shares','ph-share-network',$metrics['shares']],
                    ['Ledger Blocks','ph-cube',$metrics['ledgerBlocks']],
                ];
                foreach ($kpis as $k): ?>
                <div class="glass-card rounded-2xl p-4 animate-item">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2 text-zinc-500"><i class="ph <?php echo $k[1]; ?> text-lg"></i><span class="text-xs font-medium"><?php echo $k[0]; ?></span></div>
                        <span class="text-[10px] font-mono text-zinc-600">+0 wk</span>
                    </div>
                    <p class="text-2xl font-bold text-white tracking-tight"><?php echo (int)$k[2]; ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Main two-column: Security Center + side stack -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 mb-6">

                <!-- Security Center (Watchtower) -->
                <div class="glass-card rounded-3xl p-6 animate-item lg:col-span-2">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2"><i class="ph ph-shield-star text-lg text-green-400"></i><h3 class="text-white font-semibold tracking-tight">Security Center</h3></div>
                        <?php if ($issues > 0): ?>
                        <span class="text-[11px] font-mono text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-full border border-amber-500/20"><?php echo $issues; ?> to fix</span>
                        <?php else: ?>
                        <span class="text-[11px] font-mono text-green-400 bg-green-500/10 px-2.5 py-1 rounded-full border border-green-500/20">All clear</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-6">
                        <!-- Score ring -->
                        <div class="flex flex-col items-center justify-center shrink-0">
                            <div class="relative w-32 h-32">
                                <svg class="w-32 h-32 -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"></circle>
                                    <circle id="scoreRing" class="ring-progress" cx="50" cy="50" r="42" fill="none" stroke="<?php echo $scoreHex; ?>" stroke-width="8" stroke-linecap="round" stroke-dasharray="263.9" stroke-dashoffset="263.9"></circle>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-3xl font-bold text-white"><?php echo $score; ?></span>
                                    <span class="text-[10px] font-mono text-zinc-500">SECURITY</span>
                                </div>
                            </div>
                            <p class="text-xs text-zinc-500 mt-2 font-mono"><?php echo $okCount; ?>/<?php echo $checkTot; ?> passing</p>
                        </div>

                        <!-- Checklist -->
                        <div class="flex-1 divide-y divide-white/5">
                            <?php foreach ($checks as $c): ?>
                            <div class="flex items-center justify-between py-2.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <?php if ($c['ok']): ?>
                                        <i class="ph-fill ph-check-circle text-lg text-green-400 shrink-0"></i>
                                    <?php else: ?>
                                        <i class="ph-fill ph-warning-circle text-lg text-amber-400 shrink-0"></i>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <p class="text-sm text-white truncate"><?php echo htmlspecialchars($c['label']); ?></p>
                                        <p class="text-[11px] text-zinc-500 font-mono truncate"><?php echo htmlspecialchars($c['detail']); ?></p>
                                    </div>
                                </div>
                                <?php if (!empty($c['action'])): ?>
                                <a href="<?php echo htmlspecialchars($c['href']); ?>" class="shrink-0 text-xs font-medium text-green-400 hover:text-green-300 bg-green-500/10 hover:bg-green-500/20 border border-green-500/20 px-3 py-1.5 rounded-lg transition-colors"><?php echo htmlspecialchars($c['action']); ?></a>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Side stack: storage + getting started -->
                <div class="flex flex-col gap-4 md:gap-6">
                    <!-- Storage -->
                    <div class="glass-card rounded-3xl p-6 animate-item">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-zinc-400 text-sm font-medium">Vault Storage</h3>
                            <i class="ph ph-hard-drives text-xl text-zinc-500"></i>
                        </div>
                        <div class="flex items-end gap-2 mb-3">
                            <span class="text-2xl font-bold text-white tracking-tight"><?php echo number_format($metrics['storageUsed'],2); ?></span>
                            <span class="text-zinc-400 font-medium mb-0.5 text-sm">/ <?php echo (int)$metrics['storageTotal']; ?> GB</span>
                        </div>
                        <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden mb-2">
                            <div class="bar-fill h-full bg-gradient-to-r from-green-500 to-green-400 rounded-full" style="width:0%" data-target="<?php echo max(2,$storagePct); ?>%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-zinc-500 font-mono mb-4"><span>Free plan</span><span><?php echo $storagePct; ?>% used</span></div>
                        <button class="w-full py-2.5 text-sm font-medium text-green-400 bg-green-500/10 hover:bg-green-500/20 border border-green-500/20 rounded-xl transition-colors">Upgrade plan</button>
                    </div>

                    <!-- Getting started -->
                    <div class="glass-card rounded-3xl p-6 animate-item">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-white font-semibold tracking-tight text-sm">Getting Started</h3>
                            <span class="text-xs font-mono text-zinc-400"><?php echo $clDone; ?>/<?php echo $clTot; ?></span>
                        </div>
                        <div class="w-full h-1.5 bg-white/5 rounded-full overflow-hidden mb-4">
                            <div class="bar-fill h-full bg-gradient-to-r from-green-500 to-emerald-400 rounded-full" style="width:0%" data-target="<?php echo $clPct; ?>%"></div>
                        </div>
                        <ul class="space-y-2.5">
                            <?php foreach ($checklist as $item): ?>
                            <li class="flex items-center gap-2.5">
                                <?php if ($item['done']): ?>
                                    <i class="ph-fill ph-check-circle text-base text-green-400 shrink-0"></i><span class="text-xs text-zinc-500 line-through"><?php echo htmlspecialchars($item['label']); ?></span>
                                <?php else: ?>
                                    <i class="ph ph-circle text-base text-zinc-600 shrink-0"></i><span class="text-xs text-zinc-300"><?php echo htmlspecialchars($item['label']); ?></span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Vault categories -->
            <h2 class="text-sm font-semibold text-zinc-400 mb-3 px-1 animate-item">Your Vault</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <?php foreach ($categories as $cat): ?>
                <a href="#" class="glass-card rounded-2xl p-5 animate-item hover:bg-white/[0.07] transition-colors group">
                    <div class="w-11 h-11 rounded-xl <?php echo $cat['tint']; ?> flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                        <i class="ph <?php echo $cat['icon']; ?> text-xl"></i>
                    </div>
                    <p class="text-white font-medium tracking-tight"><?php echo htmlspecialchars($cat['label']); ?></p>
                    <p class="text-xs text-zinc-500 font-mono mt-0.5"><?php echo (int)$cat['count']; ?> items</p>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Recent items + Ledger / Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">

                <!-- Recent items -->
                <div class="glass-card rounded-3xl overflow-hidden animate-item lg:col-span-2">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                        <h3 class="text-white font-semibold tracking-tight">Recent Items</h3>
                        <a href="#" class="text-xs font-medium text-green-400 hover:text-green-300 transition-colors">View all</a>
                    </div>
                    <?php if ($metrics['files'] + $metrics['credentials'] + $metrics['secureNotes'] === 0): ?>
                    <div class="p-10 flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mb-4"><i class="ph ph-ghost text-3xl text-zinc-600"></i></div>
                        <h4 class="text-white font-medium mb-1 tracking-tight">Your vault is pristine</h4>
                        <p class="text-sm text-zinc-500 max-w-xs font-light mb-5">Upload your first file or add a credential to start building your encrypted ledger.</p>
                        <div class="flex gap-3">
                            <button class="flex items-center gap-2 bg-green-500 hover:bg-green-400 text-black font-medium text-sm px-4 py-2 rounded-xl transition-colors"><i class="ph ph-upload-simple"></i> Upload file</button>
                            <button class="flex items-center gap-2 bg-white/5 hover:bg-white/10 text-white font-medium text-sm px-4 py-2 rounded-xl border border-white/10 transition-colors"><i class="ph ph-key"></i> Add login</button>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Row template (renders when items exist) -->
                    <div class="divide-y divide-white/5">
                        <div class="data-row flex items-center justify-between px-6 py-3 hover:bg-white/5 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center text-green-400 shrink-0"><i class="ph ph-key"></i></div>
                                <div class="min-w-0"><p class="text-sm text-white truncate">Example Login</p><p class="text-[11px] text-zinc-500 font-mono truncate">updated just now</p></div>
                            </div>
                            <div class="row-actions flex items-center gap-1">
                                <button class="w-8 h-8 rounded-lg hover:bg-white/10 text-zinc-400 hover:text-white transition-colors"><i class="ph ph-copy"></i></button>
                                <button class="w-8 h-8 rounded-lg hover:bg-white/10 text-zinc-400 hover:text-white transition-colors"><i class="ph ph-share-network"></i></button>
                                <button class="w-8 h-8 rounded-lg hover:bg-white/10 text-zinc-400 hover:text-white transition-colors"><i class="ph ph-dots-three-vertical"></i></button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Ledger chain + activity -->
                <div class="glass-card rounded-3xl p-6 animate-item">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2"><i class="ph ph-cube text-lg text-green-400"></i><h3 class="text-white font-semibold tracking-tight">Ledger</h3></div>
                        <span class="flex items-center gap-1.5 text-[10px] font-mono text-green-400 bg-green-500/10 px-2 py-0.5 rounded-full border border-green-500/20"><i class="ph ph-seal-check"></i> Verified</span>
                    </div>
                    <!-- Mini chain -->
                    <div class="flex items-center mb-5">
                        <div class="shrink-0 w-20 rounded-xl p-2.5 border border-green-500/20 bg-green-500/5 text-center">
                            <span class="text-[10px] font-mono text-green-400 block">#0</span>
                            <i class="ph ph-flag-banner text-green-400"></i>
                            <span class="text-[9px] font-mono text-zinc-500 block truncate">0000…a1f9</span>
                        </div>
                        <div class="flex-1 h-px bg-gradient-to-r from-green-500/40 to-white/10 mx-1"></div>
                        <div class="shrink-0 w-20 rounded-xl p-2.5 border border-dashed border-white/10 text-center text-zinc-600">
                            <i class="ph ph-plus-circle text-lg"></i>
                            <span class="text-[9px] block mt-0.5">next</span>
                        </div>
                    </div>
                    <!-- Activity feed -->
                    <p class="text-[10px] font-mono uppercase tracking-[0.2em] text-zinc-600 mb-3">Activity</p>
                    <div class="relative pl-4 border-l border-white/10 space-y-4">
                        <div class="relative">
                            <span class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-green-500 ring-4 ring-zinc-950"></span>
                            <p class="text-sm text-white">Vault initialized</p>
                            <p class="text-[11px] text-zinc-500 font-mono">Genesis block &middot; just now</p>
                        </div>
                        <div class="relative opacity-50">
                            <span class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-zinc-700 ring-4 ring-zinc-950"></span>
                            <p class="text-sm text-zinc-400">Awaiting first action</p>
                            <p class="text-[11px] text-zinc-600 font-mono">upload, share or save</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php require_once __DIR__ . '/includes/bottom_nav.php'; ?>
</div>

<script>
    // Dropdowns
    const menus = document.querySelectorAll('[data-menu]');
    function closeAll(except){ menus.forEach(m=>{ if(m===except)return; const p=m.querySelector('[data-menu-panel]'); if(p)p.setAttribute('data-open','false'); }); }
    menus.forEach(menu=>{
        const t=menu.querySelector('[data-menu-trigger]'), p=menu.querySelector('[data-menu-panel]');
        if(!t||!p)return;
        t.addEventListener('click',e=>{ e.stopPropagation(); const open=p.getAttribute('data-open')==='true'; closeAll(menu); p.setAttribute('data-open',open?'false':'true'); });
    });
    document.addEventListener('click',()=>closeAll(null));
    document.addEventListener('keydown',e=>{ if(e.key==='Escape')closeAll(null); });

    // Greeting + live date
    const hr=new Date().getHours();
    const greet=hr<12?'Good morning':hr<18?'Good afternoon':'Good evening';
    const g=document.getElementById('greeting'); if(g)g.textContent=greet;
    const d=document.getElementById('liveDate');
    function tick(){ if(!d)return; const n=new Date(); d.textContent=n.toLocaleDateString('en-US',{weekday:'long',month:'long',day:'numeric'})+' · '+n.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'}); }
    tick(); setInterval(tick,30000);

    // Animate bars + ring on load
    window.addEventListener('load',()=>{
        document.querySelectorAll('.bar-fill[data-target]').forEach(b=>{ requestAnimationFrame(()=>{ b.style.width=b.dataset.target; }); });
        const ring=document.getElementById('scoreRing');
        if(ring){ const c=2*Math.PI*42; const score=<?php echo (int)$score; ?>; requestAnimationFrame(()=>{ ring.style.strokeDashoffset = c-(score/100)*c; }); }
    });
</script>

<script type="module">
    import { animate, stagger } from "https://cdn.jsdelivr.net/npm/motion@11.11.13/+esm";
    if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches){
        animate(".animate-item",{opacity:[0,1],y:[20,0]},{delay:stagger(0.05,{startDelay:0.1}),duration:0.6,easing:[0.22,1,0.36,1]});
    }
</script>
</body>
</html>