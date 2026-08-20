<?php 
require_once __DIR__ . '/metrics.php';

$currentPage = basename($_SERVER['PHP_SELF']); 
?>
    <!-- ============================ SIDEBAR ============================ -->
    <aside class="hidden md:flex flex-col w-64 border-r border-white/5 bg-zinc-950/80 backdrop-blur-xl z-20">
        <div class="h-20 flex items-center px-6 border-b border-white/5">
            <div class="flex items-center gap-2">
                <i class="ph ph-shield-check text-2xl text-green-500"></i>
                <span class="font-mono font-bold text-lg tracking-widest text-white">FMSS<span class="text-green-500">.</span></span>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
            <p class="px-4 pb-2 text-[10px] font-mono uppercase tracking-[0.2em] text-zinc-600">Overview</p>
            <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-medium transition-all <?php echo $currentPage === 'index.php' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5'; ?>">
                <i class="<?php echo $currentPage === 'index.php' ? 'ph-fill text-green-400' : 'ph'; ?> ph-squares-four text-xl"></i> Dashboard
            </a>

            <p class="px-4 pt-5 pb-2 text-[10px] font-mono uppercase tracking-[0.2em] text-zinc-600">Vault</p>
            <a href="logins.php" class="flex items-center justify-between px-4 py-2.5 rounded-xl font-medium transition-all <?php echo $currentPage === 'logins.php' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5'; ?>">
                <span class="flex items-center gap-3"><i class="<?php echo $currentPage === 'logins.php' ? 'ph-fill text-green-400' : 'ph'; ?> ph-key text-xl"></i> Logins</span>
                <span class="text-xs font-mono text-zinc-600"><?php echo (int)$metrics['credentials']; ?></span>
            </a>
            <a href="files.php" class="flex items-center justify-between px-4 py-2.5 rounded-xl font-medium transition-all <?php echo $currentPage === 'files.php' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5'; ?>">
                <span class="flex items-center gap-3"><i class="<?php echo $currentPage === 'files.php' ? 'ph-fill text-green-400' : 'ph'; ?> ph-folder text-xl"></i> Files</span>
                <span class="text-xs font-mono text-zinc-600"><?php echo (int)$metrics['files']; ?></span>
            </a>
            <a href="notes.php" class="flex items-center justify-between px-4 py-2.5 rounded-xl font-medium transition-all <?php echo $currentPage === 'notes.php' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5'; ?>">
                <span class="flex items-center gap-3"><i class="<?php echo $currentPage === 'notes.php' ? 'ph-fill text-green-400' : 'ph'; ?> ph-note text-xl"></i> Secure Notes</span>
                <span class="text-xs font-mono text-zinc-600"><?php echo (int)$metrics['secureNotes']; ?></span>
            </a>
            <a href="cards.php" class="flex items-center justify-between px-4 py-2.5 rounded-xl font-medium transition-all <?php echo $currentPage === 'cards.php' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5'; ?>">
                <span class="flex items-center gap-3"><i class="<?php echo $currentPage === 'cards.php' ? 'ph-fill text-green-400' : 'ph'; ?> ph-credit-card text-xl"></i> Bank Cards</span>
                <span class="text-xs font-mono text-zinc-600"><?php echo (int)($metrics['cards'] ?? 0); ?></span>
            </a>
            <a href="shares.php" class="flex items-center justify-between px-4 py-2.5 rounded-xl font-medium transition-all <?php echo $currentPage === 'shares.php' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5'; ?>">
                <span class="flex items-center gap-3"><i class="ph ph-share-network text-xl"></i> Shares</span>
                <span class="text-xs font-mono text-zinc-600"><?php echo (int)$metrics['shares']; ?></span>
            </a>

            <p class="px-4 pt-5 pb-2 text-[10px] font-mono uppercase tracking-[0.2em] text-zinc-600">Security</p>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-zinc-400 hover:text-white hover:bg-white/5 font-medium transition-all">
                <i class="ph ph-shield-star text-xl"></i> Security Center
            </a>
            <a href="ledger.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-medium transition-all <?php echo $currentPage === 'ledger.php' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5'; ?>">
                <i class="<?php echo $currentPage === 'ledger.php' ? 'ph-fill text-green-400' : 'ph'; ?> ph-tree-structure text-xl"></i> Audit Ledger
            </a>
            <a href="settings.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-medium transition-all <?php echo $currentPage === 'settings.php' ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white hover:bg-white/5'; ?>">
                <i class="<?php echo $currentPage === 'settings.php' ? 'ph-fill text-green-400' : 'ph'; ?> ph-gear-six text-xl"></i> Settings
            </a>
        </nav>

        <!-- Sidebar storage meter -->
        <div class="px-4 pb-2">
            <div class="glass-card rounded-2xl p-3.5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-medium text-zinc-400">Storage</span>
                    <span class="text-[11px] font-mono text-zinc-500"><?php echo number_format($metrics['storageUsed'],1); ?>/<?php echo (int)$metrics['storageTotal']; ?>GB</span>
                </div>
                <div class="w-full h-1.5 bg-white/5 rounded-full overflow-hidden">
                    <div class="bar-fill h-full bg-gradient-to-r from-green-500 to-green-400 rounded-full transition-all duration-1000 ease-out" style="width:<?php echo max(1, $storagePct); ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar profile -->
        <div class="p-4 border-t border-white/5">
            <div class="flex items-center gap-3 px-2 py-1.5 rounded-2xl">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-black font-bold text-sm shrink-0 shadow-[0_0_18px_rgba(34,197,94,0.35)]">
                    <?php echo htmlspecialchars($initials); ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($username); ?></p>
                    <p class="text-[11px] text-zinc-500 truncate font-mono"><?php echo htmlspecialchars($email !== '' ? $email : 'Zero-knowledge'); ?></p>
                </div>
                <a href="../logout.php" title="Sign out" class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-500 hover:text-red-400 hover:bg-red-500/10 transition-colors shrink-0"><i class="ph ph-sign-out"></i></a>
            </div>
        </div>
    </aside>