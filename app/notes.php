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

try {
    $db = \FMSS\Core\Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM secure_notes WHERE owner_id = :owner_id ORDER BY created_at DESC");
    $stmt->execute(['owner_id' => $user['id']]);
    $userNotes = $stmt->fetchAll();
} catch (Exception $e) {
    $userNotes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Secure Notes | FMSS</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['Outfit','sans-serif'], mono: ['Space Grotesk','monospace'] }, colors: { zinc: { 950: '#09090b' } } } } }
    </script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background:#000; color:#fff; -webkit-tap-highlight-color:transparent; }
        .glass-card { background: rgba(15,15,15,0.6); backdrop-filter: blur(16px); border: 1px solid rgba(34, 197, 94, 0.25); box-shadow: 0 8px 32px rgba(34, 197, 94, 0.08), inset 0 0 12px rgba(34, 197, 94, 0.02); }
        ::-webkit-scrollbar { width:0; background:transparent; }
        .menu-panel { transform-origin: top right; transition: opacity .18s ease, transform .18s cubic-bezier(0.22,1,0.36,1); }
        .menu-panel[data-open="false"] { opacity:0; transform:scale(.96) translateY(-6px); pointer-events:none; }
        .menu-panel[data-open="true"]  { opacity:1; transform:scale(1) translateY(0); pointer-events:auto; }
        .grid-texture { background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px); background-size: 44px 44px; }
        .modal-overlay { opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .modal-overlay.open { opacity: 1; pointer-events: auto; }
        .modal-content { transform: translateY(20px) scale(0.95); transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .modal-overlay.open .modal-content { transform: translateY(0) scale(1); }
    </style>
</head>
<body class="antialiased overflow-hidden selection:bg-green-500/30 selection:text-green-50">
    <div class="fixed inset-0 grid-texture z-0 pointer-events-none"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-green-500/10 blur-[120px] rounded-full z-0 pointer-events-none"></div>

    <div class="flex h-screen relative z-10">
        <?php require_once 'includes/sidebar.php'; ?>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <?php require_once 'includes/header.php'; ?>

            <main class="flex-1 overflow-y-auto pb-24 md:pb-6 relative scroll-smooth">
                <div class="max-w-5xl mx-auto p-5 md:p-8 space-y-8">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 animate-fade-in">
                        <div>
                            <h2 class="text-2xl md:text-3xl font-bold tracking-tight mb-1 text-white">Secure Notes</h2>
                            <p class="text-sm text-zinc-400 font-light">Zero-knowledge storage for your private text.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button onclick="openModal('addNoteModal')" class="flex items-center gap-2 bg-green-500 hover:bg-green-400 text-black px-5 py-2.5 rounded-xl font-semibold transition-colors shadow-[0_4px_20px_rgba(34,197,94,0.25)]">
                                <i class="ph ph-plus-circle text-xl"></i> Add Note
                            </button>
                        </div>
                    </div>

                    <!-- List -->
                    <div class="glass-card rounded-3xl overflow-hidden">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-white/5">
                            <h3 class="text-white font-semibold tracking-tight">Your Saved Notes</h3>
                            <span class="text-[10px] font-mono text-zinc-500 bg-white/5 border border-white/10 px-2 py-0.5 rounded-full"><?php echo count($userNotes); ?> items</span>
                        </div>

                        <div class="divide-y divide-white/5">
                            <?php if (empty($userNotes)): ?>
                            <div class="p-10 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mb-4"><i class="ph ph-note text-3xl text-zinc-600"></i></div>
                                <h4 class="text-white font-medium mb-1 tracking-tight">No notes yet</h4>
                                <p class="text-sm text-zinc-500 max-w-xs font-light">Add your first secure note.</p>
                            </div>
                            <?php else: ?>
                                <?php foreach ($userNotes as $note): ?>
                                <div class="flex items-center justify-between px-6 py-4 hover:bg-white/5 transition-colors group cursor-pointer" onclick="viewNote('<?php echo $note['id']; ?>', '<?php echo htmlspecialchars(addslashes($note['title'])); ?>')">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="w-11 h-11 rounded-xl bg-green-500/10 flex items-center justify-center text-green-400 shrink-0 group-hover:scale-105 transition-transform">
                                            <i class="ph ph-note text-xl"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-white truncate mb-0.5"><?php echo htmlspecialchars($note['title']); ?></p>
                                            <p class="text-[11px] text-zinc-500 font-mono">Added <?php echo date('M j, Y', strtotime($note['created_at'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg hover:bg-red-500/10 text-zinc-400 hover:text-red-400 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100" onclick="event.stopPropagation(); deleteNote('<?php echo $note['id']; ?>')" title="Delete"><i class="ph ph-trash"></i></div>
                                        <i class="ph ph-caret-right text-zinc-600 group-hover:text-green-400 transition-colors"></i>
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

    <!-- ADD NOTE MODAL -->
    <div id="addNoteModal" class="modal-overlay fixed inset-0 z-[100] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="modal-content w-full h-full sm:h-auto sm:max-w-2xl bg-zinc-950 sm:glass-card sm:rounded-3xl flex flex-col shadow-2xl border-t sm:border border-white/10 relative">
            <div class="flex items-center justify-between p-5 border-b border-white/5">
                <h3 class="text-lg font-semibold text-white">Add Secure Note</h3>
                <button onclick="closeModal('addNoteModal')" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-zinc-400 hover:text-white transition-colors">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            <div class="p-5 flex-1 overflow-y-auto">
                <form id="addNoteForm" class="flex flex-col gap-4 h-full">
                    
                    <!-- STEP 1: Details -->
                    <div id="addNoteStep1" class="flex flex-col gap-4 h-full">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-zinc-400">Title</label>
                            <input type="text" id="addTitle" placeholder="e.g. Recovery Phrases" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-green-500/50 outline-none">
                        </div>
                        <div class="space-y-1.5 flex-1 flex flex-col">
                            <label class="text-xs font-medium text-zinc-400">Note Content</label>
                            <textarea id="addContent" rows="6" class="flex-1 w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:border-green-500/50 outline-none resize-none font-mono"></textarea>
                        </div>
                        <button type="button" onclick="document.getElementById('addNoteStep1').classList.add('hidden'); document.getElementById('addNoteStep2').classList.remove('hidden');" class="w-full mt-2 py-3.5 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition-colors flex items-center justify-center gap-2">
                            Next Step <i class="ph ph-arrow-right"></i>
                        </button>
                    </div>

                    <!-- STEP 2: Encryption -->
                    <div id="addNoteStep2" class="hidden flex flex-col gap-4 h-full">
                        <button type="button" onclick="document.getElementById('addNoteStep2').classList.add('hidden'); document.getElementById('addNoteStep1').classList.remove('hidden');" class="self-start text-xs text-zinc-400 hover:text-white flex items-center gap-1 transition-colors mb-2">
                            <i class="ph ph-arrow-left"></i> Back to details
                        </button>
                        
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-green-400">Master Password</label>
                            <div class="relative">
                                <i class="ph ph-lock absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-lg"></i>
                                <input type="password" id="addMasterPassword" placeholder="Required to encrypt..." class="w-full bg-white/5 border border-white/10 rounded-xl pl-11 pr-10 py-3 text-sm text-white focus:border-green-500/50 outline-none">
                                <button type="button" tabindex="-1" onclick="toggleVis('addMasterPassword', this.querySelector('i'))" class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <i class="ph ph-eye text-zinc-400 hover:text-white transition-colors"></i>
                                </button>
                            </div>
                            <p class="text-[11px] text-zinc-500 mt-2">Your note will be encrypted locally before being sent to the server. FMSS cannot access this data.</p>
                        </div>

                        <button type="button" onclick="submitAddNote()" id="addNoteBtn" class="w-full mt-4 py-3.5 bg-green-500 hover:bg-green-400 text-black font-semibold rounded-xl transition-colors shadow-[0_4px_20px_rgba(34,197,94,0.3)] flex items-center justify-center gap-2">
                            <i class="ph ph-lock-key"></i> Encrypt & Save
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- VIEW/DECRYPT MODAL -->
    <div id="viewNoteModal" class="modal-overlay fixed inset-0 z-[100] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="modal-content w-full h-full sm:h-auto sm:max-w-2xl bg-zinc-950 sm:glass-card sm:rounded-3xl flex flex-col shadow-2xl border-t sm:border border-white/10 relative">
            <div class="flex items-center justify-between p-5 border-b border-white/5">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2"><i class="ph ph-shield-check text-green-400"></i> Decrypt Note</h3>
                <button onclick="closeModal('viewNoteModal')" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-zinc-400 hover:text-white transition-colors">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            
            <div class="p-5 flex flex-col gap-5">
                <p class="text-sm text-zinc-400" id="viewNoteTitleLabel">Enter master password to unlock <span id="viewNoteTarget" class="text-white font-bold"></span>.</p>
                <input type="hidden" id="viewNoteId">
                
                <div id="viewNoteAuthBox">
                    <div class="relative mb-4">
                        <i class="ph ph-lock absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-lg"></i>
                        <input type="password" id="viewMasterPassword" placeholder="Master Password" class="w-full bg-white/5 border border-white/10 rounded-xl pl-11 pr-10 py-3 text-sm text-white focus:border-green-500/50 outline-none">
                        <button type="button" tabindex="-1" onclick="toggleVis('viewMasterPassword', this.querySelector('i'))" class="absolute right-3 top-1/2 -translate-y-1/2">
                            <i class="ph ph-eye text-zinc-400 hover:text-white transition-colors"></i>
                        </button>
                    </div>
                    <button type="button" onclick="submitViewNote()" id="viewNoteBtn" class="w-full py-3 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition-colors flex items-center justify-center gap-2">
                        Unlock
                    </button>
                </div>

                <div id="viewNoteDataBox" class="hidden flex flex-col gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="text-[10px] uppercase tracking-widest text-zinc-500 font-mono">Content</label>
                            <button onclick="copyVal('viewContent')" class="text-zinc-500 hover:text-green-400 transition-colors"><i class="ph ph-copy"></i> Copy</button>
                        </div>
                        <textarea id="viewContent" readonly rows="10" class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-sm text-green-400 font-mono outline-none resize-none"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inject data for JS to use -->
    <script>
        window.userNotesData = <?php echo json_encode($userNotes); ?>;
    </script>

    <script type="module">
        import { CryptoManager } from '../assets/js/crypto.js';

        window.openModal = id => document.getElementById(id).classList.add('open');
        window.closeModal = id => document.getElementById(id).classList.remove('open');
        window.toggleVis = (id, icon) => {
            const el = document.getElementById(id);
            if(el.type === 'password') { el.type = 'text'; icon.className = 'ph ph-eye-slash text-zinc-400 hover:text-white transition-colors'; }
            else { el.type = 'password'; icon.className = 'ph ph-eye text-zinc-400 hover:text-white transition-colors'; }
        };
        window.copyVal = (id) => {
            const el = document.getElementById(id);
            navigator.clipboard.writeText(el.value);
        };

        window.submitAddNote = async () => {
            const btn = document.getElementById('addNoteBtn');
            const pwd = document.getElementById('addMasterPassword').value;
            const title = document.getElementById('addTitle').value;
            if(!title || !pwd) return alert("Title and Master Password required.");

            const payload = JSON.stringify({
                content: document.getElementById('addContent').value
            });

            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Encrypting...';
            btn.disabled = true;

            try {
                const masterKey = await CryptoManager.deriveMasterKey(pwd);
                const { ciphertextHex, iv, wrappedDataKey } = await CryptoManager.encryptString(payload, masterKey);

                const response = await fetch('api/note_create.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        title: title,
                        encrypted_payload: ciphertextHex,
                        wrapped_data_key: wrappedDataKey,
                        iv: iv
                    })
                });

                const res = await response.json();
                if(res.success) {
                    window.location.reload();
                } else throw new Error(res.error);
            } catch (e) {
                alert("Action failed: " + e.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };

        window.viewNote = (id, title) => {
            document.getElementById('viewNoteId').value = id;
            document.getElementById('viewNoteTarget').textContent = title;
            document.getElementById('viewMasterPassword').value = '';
            document.getElementById('viewNoteAuthBox').classList.remove('hidden');
            document.getElementById('viewNoteDataBox').classList.add('hidden');
            openModal('viewNoteModal');
        };

        window.submitViewNote = async () => {
            const btn = document.getElementById('viewNoteBtn');
            const id = document.getElementById('viewNoteId').value;
            const pwd = document.getElementById('viewMasterPassword').value;
            if(!pwd) return;

            const note = window.userNotesData.find(n => n.id === id);
            if(!note) return alert("Error finding note data.");

            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Decrypting...';
            btn.disabled = true;

            try {
                const masterKey = await CryptoManager.deriveMasterKey(pwd);
                const plaintext = await CryptoManager.decryptString(note.encrypted_payload, note.iv, note.encrypted_data_key, masterKey);
                const data = JSON.parse(plaintext);
                
                document.getElementById('viewContent').value = data.content || '';

                document.getElementById('viewNoteAuthBox').classList.add('hidden');
                document.getElementById('viewNoteDataBox').classList.remove('hidden');
            } catch (e) {
                alert("Decryption failed. Wrong password?");
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };

        window.deleteNote = async (id) => {
            if(!confirm("Are you sure you want to permanently delete this note?")) return;
            try {
                const response = await fetch('api/note_delete.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id})
                });
                const res = await response.json();
                if(res.success) window.location.reload();
                else alert(res.error);
            } catch (e) {
                alert(e.message);
            }
        };

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
    </script>
</body>
</html>
