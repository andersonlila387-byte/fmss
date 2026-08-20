<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <!-- Mobile bottom nav -->
    <nav class="md:hidden fixed bottom-0 w-full h-[68px] glass-card z-50 flex justify-around items-center px-2 shadow-[0_-10px_40px_rgba(0,0,0,0.3)] pb-safe border-t border-white/10 border-l-0 border-r-0 border-b-0 rounded-none">
        
        <a href="index.php" class="flex flex-col items-center justify-center gap-1 p-1 min-w-[64px] group <?php echo $currentPage === 'index.php' ? 'text-green-400' : 'text-zinc-500 hover:text-white'; ?> transition-colors">
            <div class="relative">
                <i class="<?php echo $currentPage === 'index.php' ? 'ph-fill' : 'ph'; ?> ph-squares-four text-[22px] relative z-10 group-hover:-translate-y-1 transition-transform"></i>
                <?php if ($currentPage === 'index.php'): ?><div class="absolute inset-0 bg-green-400/30 blur-md rounded-full -z-0"></div><?php endif; ?>
            </div>
            <span class="text-[10px] font-medium tracking-wide">Home</span>
        </a>

        <a href="files.php" class="flex flex-col items-center justify-center gap-1 p-1 min-w-[64px] group <?php echo $currentPage === 'files.php' ? 'text-green-400' : 'text-zinc-500 hover:text-white'; ?> transition-colors">
            <div class="relative">
                <i class="<?php echo $currentPage === 'files.php' ? 'ph-fill' : 'ph'; ?> ph-folder text-[22px] relative z-10 group-hover:-translate-y-1 transition-transform"></i>
                <?php if ($currentPage === 'files.php'): ?><div class="absolute inset-0 bg-green-400/30 blur-md rounded-full -z-0"></div><?php endif; ?>
            </div>
            <span class="text-[10px] font-medium tracking-wide">Vault</span>
        </a>

        <div class="relative -top-5">
            <button onclick="if(typeof openModal === 'function') { openModal('uploadModal'); } else { window.location.href='files.php'; }" class="w-14 h-14 bg-green-500 text-black rounded-full flex items-center justify-center shadow-[0_8px_30px_rgba(34,197,94,0.3)] hover:scale-105 active:scale-95 transition-all outline-none border-[4px] border-[#09090b]">
                <i class="ph ph-plus text-2xl font-bold"></i>
            </button>
        </div>

        <a href="logins.php" class="flex flex-col items-center justify-center gap-1 p-1 min-w-[64px] group <?php echo $currentPage === 'logins.php' ? 'text-green-400' : 'text-zinc-500 hover:text-white'; ?> transition-colors">
            <div class="relative">
                <i class="<?php echo $currentPage === 'logins.php' ? 'ph-fill' : 'ph'; ?> ph-key text-[22px] relative z-10 group-hover:-translate-y-1 transition-transform"></i>
                <?php if ($currentPage === 'logins.php'): ?><div class="absolute inset-0 bg-green-400/30 blur-md rounded-full -z-0"></div><?php endif; ?>
            </div>
            <span class="text-[10px] font-medium tracking-wide">Logins</span>
        </a>

        <a href="ledger.php" class="flex flex-col items-center justify-center gap-1 p-1 min-w-[64px] group <?php echo $currentPage === 'ledger.php' ? 'text-green-400' : 'text-zinc-500 hover:text-white'; ?> transition-colors">
            <div class="relative">
                <i class="<?php echo $currentPage === 'ledger.php' ? 'ph-fill' : 'ph'; ?> ph-tree-structure text-[22px] relative z-10 group-hover:-translate-y-1 transition-transform"></i>
                <?php if ($currentPage === 'ledger.php'): ?><div class="absolute inset-0 bg-green-400/30 blur-md rounded-full -z-0"></div><?php endif; ?>
            </div>
            <span class="text-[10px] font-medium tracking-wide">Ledger</span>
        </a>
    </nav>