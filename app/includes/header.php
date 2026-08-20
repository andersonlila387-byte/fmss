<?php
$memberSince = '';
if (!empty($user['created_at'])) {
    $ts = strtotime((string) $user['created_at']);
    if ($ts) { $memberSince = date('M Y', $ts); }
}
$statusStyles = [
    'active'    => ['label' => 'Active',    'class' => 'text-green-400 bg-green-500/10 border-green-500/20'],
    'pending'   => ['label' => 'Pending',   'class' => 'text-amber-400 bg-amber-500/10 border-amber-500/20'],
    'suspended' => ['label' => 'Suspended', 'class' => 'text-red-400 bg-red-500/10 border-red-500/20'],
    'locked'    => ['label' => 'Locked',    'class' => 'text-red-600 bg-red-600/10 border-red-600/20'],
];
$statusStr = strtolower($user['status'] ?? 'active');
if (!isset($statusMeta)) {
    $statusMeta = $statusStyles[$statusStr] ?? $statusStyles['active'];
}
?>
        <!-- Desktop header -->
        <header class="hidden md:flex h-20 items-center justify-between px-8 border-b border-white/5 bg-zinc-950/40 backdrop-blur-xl sticky top-0 z-30">
            <div>
                <?php 
                $currentPage = basename($_SERVER['PHP_SELF']);
                $pageTitles = [
                    'index.php' => 'Dashboard',
                    'files.php' => 'Files Vault',
                    'logins.php' => 'Logins',
                    'ledger.php' => 'Audit Ledger'
                ];
                $title = $pageTitles[$currentPage] ?? 'Dashboard';
                ?>
                <h1 class="text-lg font-bold text-white tracking-tight"><?php echo htmlspecialchars($title); ?></h1>
                <p id="liveDate" class="text-xs text-zinc-500 font-mono"></p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative w-72">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500"></i>
                    <input type="text" placeholder="Search vault, logins, hashes…" class="w-full bg-white/5 border border-white/5 rounded-xl pl-11 pr-12 py-2.5 text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-green-500/40 focus:bg-white/10 transition-all">
                    <kbd class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-mono text-zinc-500 bg-white/5 border border-white/10 rounded px-1.5 py-0.5">⌘K</kbd>
                </div>
                <!-- Notifications -->
                <div class="relative" data-menu>
                    <button data-menu-trigger class="relative w-11 h-11 rounded-xl glass-card flex items-center justify-center text-zinc-300 hover:text-white hover:bg-white/10 transition-all">
                        <i class="ph ph-bell text-xl"></i>
                        <span class="absolute top-2.5 right-2.5 w-2 h-2 rounded-full bg-green-500 ring-2 ring-zinc-950"></span>
                    </button>
                    <div data-menu-panel data-open="false" class="menu-panel absolute right-0 mt-2 w-80 bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-white/5 flex items-center justify-between">
                            <span class="text-sm font-semibold text-white">Notifications</span>
                            <span class="text-[10px] font-mono text-green-400 bg-green-500/10 px-2 py-0.5 rounded-full">2 new</span>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            <a href="#" class="flex gap-3 px-4 py-3 hover:bg-white/5 transition-colors">
                                <div class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center text-green-400 shrink-0"><i class="ph ph-shield-check"></i></div>
                                <div class="min-w-0"><p class="text-sm text-white">Vault initialized securely</p><p class="text-xs text-zinc-500">Genesis block written to your ledger.</p></div>
                            </a>
                            <a href="#" class="flex gap-3 px-4 py-3 hover:bg-white/5 transition-colors">
                                <div class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400 shrink-0"><i class="ph ph-lifebuoy"></i></div>
                                <div class="min-w-0"><p class="text-sm text-white">Download your recovery kit</p><p class="text-xs text-zinc-500">Required to restore a zero-knowledge vault.</p></div>
                            </a>
                        </div>
                        <a href="#" class="block text-center text-xs font-medium text-green-400 hover:text-green-300 py-3 border-t border-white/5 transition-colors">View all activity</a>
                    </div>
                </div>
                <!-- Profile -->
                <div class="relative" data-menu>
                    <button data-menu-trigger class="flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-xl glass-card hover:bg-white/10 transition-all">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-black font-bold text-sm"><?php echo htmlspecialchars($initials); ?></div>
                        <span class="text-sm font-medium text-white max-w-[120px] truncate"><?php echo htmlspecialchars($username); ?></span>
                        <i class="ph ph-caret-down text-zinc-500 text-sm"></i>
                    </button>
                    <div data-menu-panel data-open="false" class="menu-panel absolute right-0 mt-2 w-72 bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl overflow-hidden z-50">
                        <div class="p-4 border-b border-white/5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-black font-bold"><?php echo htmlspecialchars($initials); ?></div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($username); ?></p>
                                    <p class="text-xs text-zinc-500 truncate"><?php echo htmlspecialchars($email !== '' ? $email : 'no email on file'); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-3">
                                <span class="text-[10px] font-mono uppercase tracking-wide px-2 py-1 rounded-full border <?php echo $statusMeta['class']; ?>"><?php echo htmlspecialchars($statusMeta['label']); ?></span>
                                <?php if ($memberSince !== ''): ?><span class="text-[10px] font-mono text-zinc-500">Since <?php echo htmlspecialchars($memberSince); ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="p-2">
                            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-300 hover:text-white hover:bg-white/5 transition-colors"><i class="ph ph-user-circle text-lg"></i> Profile</a>
                            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-300 hover:text-white hover:bg-white/5 transition-colors"><i class="ph ph-gear-six text-lg"></i> Settings</a>
                            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-300 hover:text-white hover:bg-white/5 transition-colors"><i class="ph ph-lifebuoy text-lg"></i> Recovery Kit</a>
                        </div>
                        <div class="p-2 border-t border-white/5">
                            <a href="../logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-400 hover:bg-red-500/10 transition-colors"><i class="ph ph-sign-out text-lg"></i> Sign out</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile header -->
        <header class="md:hidden h-16 border-b border-white/5 bg-zinc-950/80 backdrop-blur-xl flex items-center justify-between px-5 sticky top-0 z-40">
            <div class="flex items-center gap-2"><i class="ph ph-shield-check text-xl text-green-500"></i><span class="font-mono font-bold tracking-widest text-white">FMSS<span class="text-green-500">.</span></span></div>
            <div class="flex items-center gap-2">
                <button class="relative w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-zinc-300"><i class="ph ph-bell"></i><span class="absolute top-2 right-2 w-1.5 h-1.5 rounded-full bg-green-500"></span></button>
                <div class="relative" data-menu>
                    <button data-menu-trigger class="w-9 h-9 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-black font-bold text-xs"><?php echo htmlspecialchars($initials); ?></button>
                    <div data-menu-panel data-open="false" class="menu-panel absolute right-0 mt-2 w-60 bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl overflow-hidden z-50">
                        <div class="p-4 border-b border-white/5"><p class="text-sm font-semibold text-white truncate"><?php echo htmlspecialchars($username); ?></p><p class="text-xs text-zinc-500 truncate"><?php echo htmlspecialchars($email !== '' ? $email : 'Zero-knowledge'); ?></p></div>
                        <div class="p-2">
                            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-300 hover:bg-white/5"><i class="ph ph-user-circle text-lg"></i> Profile</a>
                            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-300 hover:bg-white/5"><i class="ph ph-gear-six text-lg"></i> Settings</a>
                            <a href="../logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-400 hover:bg-red-500/10"><i class="ph ph-sign-out text-lg"></i> Sign out</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Inactivity Lock Script -->
        <script>
            (function() {
                const INACTIVITY_TIMEOUT_MS = 15 * 60 * 1000; // 15 minutes
                let inactivityTimer;

                const resetTimer = () => {
                    clearTimeout(inactivityTimer);
                    inactivityTimer = setTimeout(() => {
                        // Redirect to logout due to inactivity
                        window.location.href = '../logout.php?reason=inactivity';
                    }, INACTIVITY_TIMEOUT_MS);
                };

                // Listen for activity
                ['mousemove', 'mousedown', 'keypress', 'touchmove', 'scroll'].forEach(evt => {
                    document.addEventListener(evt, resetTimer, { passive: true });
                });

                resetTimer(); // Start initial timer
            })();
        </script>