<?php
require_once __DIR__ . '/../src/Core/SessionManager.php';
require_once __DIR__ . '/../src/Core/Database.php';

// Protect this route: user must be logged in
$user = \FMSS\Core\SessionManager::requireAuthentication();

// Display values
$username = $user['username'] ?? 'User';
$email    = $user['email']    ?? '';
$status   = strtolower($user['status'] ?? 'active');

$parts    = preg_split('/[\s._\-]+/', trim($username));
$first    = $parts[0] ?? 'U';
$second   = $parts[1] ?? '';
$initials = strtoupper(substr($first, 0, 1) . ($second !== '' ? substr($second, 0, 1) : substr($first, 1, 1)));
$initials = $initials !== '' ? $initials : 'U';

// Fetch files from DB
try {
    $db = \FMSS\Core\Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM files WHERE owner_id = :owner_id AND is_deleted = FALSE ORDER BY created_at DESC");
    $stmt->execute(['owner_id' => $user['id']]);
    $userFiles = $stmt->fetchAll();
} catch (Exception $e) {
    $userFiles = [];
}

// Helpers
function formatBytes($bytes, $precision = 1) { 
    $units = ['B', 'KB', 'MB', 'GB', 'TB']; 
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow]; 
}

function getFileMeta($mimeType) {
    if (strpos($mimeType, 'image/') !== false) return ['icon' => 'ph-image', 'color' => 'text-blue-400', 'bg' => 'bg-blue-500/10'];
    if (strpos($mimeType, 'pdf') !== false) return ['icon' => 'ph-file-pdf', 'color' => 'text-red-400', 'bg' => 'bg-red-500/10'];
    if (strpos($mimeType, 'zip') !== false || strpos($mimeType, 'rar') !== false) return ['icon' => 'ph-file-archive', 'color' => 'text-amber-400', 'bg' => 'bg-amber-500/10'];
    if (strpos($mimeType, 'video/') !== false) return ['icon' => 'ph-video-camera', 'color' => 'text-purple-400', 'bg' => 'bg-purple-500/10'];
    if (strpos($mimeType, 'text/') !== false) return ['icon' => 'ph-file-text', 'color' => 'text-zinc-400', 'bg' => 'bg-zinc-500/10'];
    return ['icon' => 'ph-file', 'color' => 'text-green-400', 'bg' => 'bg-green-500/10'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Files Vault | FMSS</title>

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
        .menu-panel { transform-origin: top right; transition: opacity .18s ease, transform .18s cubic-bezier(0.22,1,0.36,1); }
        .menu-panel[data-open="false"] { opacity:0; transform:scale(.96) translateY(-6px); pointer-events:none; }
        .menu-panel[data-open="true"]  { opacity:1; transform:scale(1) translateY(0); pointer-events:auto; }
        .grid-texture {
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 44px 44px;
        }
        
        /* Modal Transitions */
        .modal-overlay { opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .modal-overlay.open { opacity: 1; pointer-events: auto; }
        .modal-content { transform: translateY(20px) scale(0.95); transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .modal-overlay.open .modal-content { transform: translateY(0) scale(1); }

        @media (prefers-reduced-motion: reduce) { *, .menu-panel, .modal-overlay, .modal-content { transition:none!important; animation:none!important; } }
    </style>
</head>
<body class="antialiased overflow-hidden selection:bg-green-500/30 selection:text-green-50">

<div class="flex h-screen w-full relative">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-full relative z-10 grid-texture">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-green-500/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>
        
        <?php require_once __DIR__ . '/includes/header.php'; ?>

        <div class="flex-1 overflow-y-auto p-5 md:p-8 pb-32 md:pb-8">

            <div class="flex items-center justify-between gap-4 mb-8 animate-item">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-1 tracking-tight">Files Vault</h1>
                    <p class="text-xs font-mono text-zinc-400">Encrypted with AES-256-GCM. Fingerprinted with SHA-256.</p>
                </div>
            </div>

            <!-- Upload Area Trigger -->
            <div onclick="openModal('uploadModal')" class="glass-card rounded-3xl border-dashed border-2 border-white/10 hover:border-green-500/50 transition-colors p-10 flex flex-col items-center justify-center text-center group cursor-pointer mb-8 animate-item">
                <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="ph ph-upload-simple text-3xl text-green-400"></i>
                </div>
                <h3 class="text-white font-medium tracking-tight text-lg mb-1">Upload Secure File</h3>
                <p class="text-sm text-zinc-500 max-w-md font-light">Click to upload. Files will be encrypted locally before upload.</p>
            </div>

            <!-- File List -->
            <div class="glass-card rounded-3xl overflow-hidden animate-item">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 border-b border-white/5 gap-4">
                    <div class="flex items-center gap-3">
                        <h3 class="text-white font-semibold tracking-tight">Your Files</h3>
                        <span class="text-[10px] font-mono text-zinc-500 bg-white/5 border border-white/10 px-2 py-0.5 rounded-full"><?php echo count($userFiles); ?> items</span>
                    </div>
                </div>

                <div class="divide-y divide-white/5">
                    <?php if (empty($userFiles)): ?>
                    <div class="p-10 flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mb-4"><i class="ph ph-ghost text-3xl text-zinc-600"></i></div>
                        <h4 class="text-white font-medium mb-1 tracking-tight">No files yet</h4>
                        <p class="text-sm text-zinc-500 max-w-xs font-light">Upload a file to securely store it in your zero-knowledge vault.</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($userFiles as $file): 
                            $meta = getFileMeta($file['mime_type']);
                            $dateStr = date('M j, Y', strtotime($file['created_at']));
                            $shortHash = substr($file['file_hash'], 0, 4) . '...' . substr($file['file_hash'], -4);
                        ?>
                        <div class="flex items-center justify-between px-6 py-4 hover:bg-white/5 transition-colors group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-11 h-11 rounded-xl <?php echo $meta['bg']; ?> flex items-center justify-center <?php echo $meta['color']; ?> shrink-0 group-hover:scale-105 transition-transform">
                                    <i class="ph <?php echo $meta['icon']; ?> text-xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-white truncate mb-0.5"><?php echo htmlspecialchars($file['original_name']); ?></p>
                                    <div class="flex items-center gap-3 text-[11px] text-zinc-500 font-mono">
                                        <span><?php echo formatBytes($file['size']); ?></span>
                                        <span class="w-1 h-1 rounded-full bg-zinc-700"></span>
                                        <span><?php echo $dateStr; ?></span>
                                        <span class="w-1 h-1 rounded-full bg-zinc-700 hidden sm:block"></span>
                                        <span class="hidden sm:flex items-center gap-1 text-zinc-600" title="SHA-256 Fingerprint: <?php echo htmlspecialchars($file['file_hash']); ?>"><i class="ph ph-fingerprint text-green-400/70"></i> <?php echo htmlspecialchars($shortHash); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                <button onclick="requestAction('share', '<?php echo $file['id']; ?>', '<?php echo htmlspecialchars(addslashes($file['original_name'])); ?>')" class="w-8 h-8 rounded-lg hover:bg-green-500/10 text-zinc-400 hover:text-green-400 transition-colors flex items-center justify-center" title="Share Link"><i class="ph ph-share-network"></i></button>
                                <button onclick="requestAction('download', '<?php echo $file['id']; ?>', '<?php echo htmlspecialchars(addslashes($file['original_name'])); ?>')" class="w-8 h-8 rounded-lg hover:bg-white/10 text-zinc-400 hover:text-white transition-colors flex items-center justify-center" title="Download & Verify"><i class="ph ph-download-simple"></i></button>
                                <button onclick="requestAction('edit', '<?php echo $file['id']; ?>', '<?php echo htmlspecialchars(addslashes($file['original_name'])); ?>')" class="w-8 h-8 rounded-lg hover:bg-white/10 text-zinc-400 hover:text-white transition-colors flex items-center justify-center" title="Edit Properties"><i class="ph ph-pencil"></i></button>
                                <button onclick="requestAction('delete', '<?php echo $file['id']; ?>', '<?php echo htmlspecialchars(addslashes($file['original_name'])); ?>')" class="w-8 h-8 rounded-lg hover:bg-red-500/10 text-zinc-400 hover:text-red-400 transition-colors flex items-center justify-center" title="Delete"><i class="ph ph-trash"></i></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <?php require_once __DIR__ . '/includes/bottom_nav.php'; ?>
</div>

<!-- UPLOAD MODAL (Full page on mobile, centered on desktop) -->
<div id="uploadModal" class="modal-overlay fixed inset-0 z-[100] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-content w-full h-full sm:h-auto sm:max-w-md bg-zinc-950 sm:glass-card sm:rounded-3xl flex flex-col shadow-2xl border-t sm:border border-white/10 relative">
        <div class="flex items-center justify-between p-5 border-b border-white/5">
            <h3 class="text-lg font-semibold text-white">Upload File</h3>
            <button onclick="closeModal('uploadModal')" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-zinc-400 hover:text-white transition-colors">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="p-5 flex-1 overflow-y-auto">
            <form id="uploadForm" class="flex flex-col gap-5 h-full">
                <!-- File Drag & Drop -->
                <div class="border-2 border-dashed border-white/10 hover:border-green-500/50 rounded-2xl p-6 flex flex-col items-center justify-center transition-colors text-center group">
                    <label class="flex flex-col items-center justify-center cursor-pointer w-full">
                        <div class="w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="ph ph-file-plus text-2xl text-green-400"></i>
                        </div>
                        <span id="uploadLabelText" class="text-sm text-white font-medium mb-1">Select a file</span>
                        <span class="text-xs text-zinc-500 mb-4">or drag and drop here</span>
                        <input type="file" id="fileInput" class="hidden" onchange="
                            document.getElementById('uploadLabelText').textContent = this.files[0]?.name || 'Select a file';
                            document.getElementById('uploadLabelText').classList.add('text-green-400');
                            if(document.getElementById('fileName').value === '') document.getElementById('fileName').value = this.files[0]?.name || '';
                        ">
                    </label>
                    
                    <!-- Custom Name Input inside the box -->
                    <div class="w-full mt-2 pt-4 border-t border-white/5 text-left">
                        <label class="text-xs font-medium text-zinc-400 mb-1.5 block">Custom Name (for searching)</label>
                        <input type="text" id="fileName" placeholder="e.g. School Certificate..." class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-green-500/50 transition-colors">
                    </div>
                </div>
                
                <!-- Master Password Input -->
                <div class="space-y-1.5 flex-1">
                    <label class="text-xs font-medium text-zinc-400">Master Password</label>
                    <div class="relative">
                        <i class="ph ph-lock absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-lg"></i>
                        <input type="password" id="uploadPassword" placeholder="Required to encrypt..." class="w-full bg-white/5 border border-white/10 rounded-xl pl-11 pr-12 py-3 text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-green-500/50 transition-colors">
                        <button type="button" tabindex="-1" onclick="const p=document.getElementById('uploadPassword'); const i=this.querySelector('i'); if(p.type==='password'){p.type='text';i.className='ph ph-eye-slash text-zinc-400 hover:text-white transition-colors';}else{p.type='password';i.className='ph ph-eye text-zinc-400 hover:text-white transition-colors';}" class="absolute right-4 top-1/2 -translate-y-1/2 focus:outline-none">
                            <i class="ph ph-eye text-zinc-400 hover:text-white transition-colors"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="p-5 border-t border-white/5">
            <button type="button" onclick="submitUpload()" id="uploadBtn" class="w-full py-3.5 bg-green-500 hover:bg-green-400 text-black font-semibold rounded-xl transition-colors shadow-[0_4px_20px_rgba(34,197,94,0.3)] flex items-center justify-center gap-2">
                <i class="ph ph-lock-key"></i> Encrypt & Upload
            </button>
        </div>
    </div>
</div>

<!-- SECURITY ACTION MODAL (Password prompt) -->
<div id="securityModal" class="modal-overlay fixed inset-0 z-[100] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="modal-content w-full sm:w-auto sm:min-w-[400px] bg-zinc-950 sm:glass-card sm:rounded-3xl flex flex-col shadow-2xl border-t sm:border border-white/10 relative pb-safe">
        <div class="flex items-center justify-between p-5 border-b border-white/5">
            <h3 class="text-lg font-semibold text-white" id="securityActionTitle">Security Verification</h3>
            <button onclick="closeModal('securityModal')" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-zinc-400 hover:text-white transition-colors">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="p-5">
            <p class="text-sm text-zinc-400 mb-5">Please enter your master password to <span id="securityActionText" class="font-bold text-white">perform this action</span> on <span id="securityTargetFile" class="font-bold text-white"></span>.</p>
            
            <form id="securityForm" class="space-y-5">
                <input type="hidden" id="securityActionType">
                <input type="hidden" id="securityFileId">
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-zinc-400">Master Password</label>
                    <div class="relative">
                        <i class="ph ph-lock absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-lg"></i>
                        <input type="password" id="masterPassword" placeholder="Enter your password..." class="w-full bg-white/5 border border-white/10 rounded-xl pl-11 pr-12 py-3 text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-green-500/50 transition-colors">
                        <button type="button" tabindex="-1" onclick="const p=document.getElementById('masterPassword'); const i=this.querySelector('i'); if(p.type==='password'){p.type='text';i.className='ph ph-eye-slash text-zinc-400 hover:text-white transition-colors';}else{p.type='password';i.className='ph ph-eye text-zinc-400 hover:text-white transition-colors';}" class="absolute right-4 top-1/2 -translate-y-1/2 focus:outline-none">
                            <i class="ph ph-eye text-zinc-400 hover:text-white transition-colors"></i>
                        </button>
                    </div>
                </div>
                
                <div id="shareLinkContainer" class="hidden space-y-1.5 pt-2">
                    <label class="text-xs font-medium text-green-400">Secure Share Link</label>
                    <input type="text" id="shareLinkResult" readonly class="w-full bg-black/50 border border-green-500/30 rounded-xl px-4 py-3 text-sm text-white font-mono focus:outline-none" onclick="this.select()">
                </div>
                
                <button type="button" onclick="submitSecurityAction()" id="securityActionBtn" class="w-full py-3.5 bg-green-500 hover:bg-green-400 text-black font-semibold rounded-xl transition-colors shadow-[0_4px_20px_rgba(34,197,94,0.3)] flex items-center justify-center gap-2">
                    <i class="ph ph-check-circle"></i> Confirm & Proceed
                </button>
            </form>
        </div>
    </div>
</div>

<script type="module">
    import { CryptoManager } from '../assets/js/crypto.js';

    window.openModal = function(id) {
        document.getElementById(id).classList.add('open');
    }
    
    window.closeModal = function(id) {
        document.getElementById(id).classList.remove('open');
    }

    window.requestAction = function(action, fileId, fileName) {
        document.getElementById('securityActionType').value = action;
        document.getElementById('securityFileId').value = fileId;
        document.getElementById('securityTargetFile').textContent = fileName;
        
        let verb = action;
        if (action === 'download') verb = 'decrypt and download';
        if (action === 'share') verb = 'generate a secure share link for';
        document.getElementById('securityActionText').textContent = verb;
        document.getElementById('masterPassword').value = ''; 
        
        document.getElementById('shareLinkContainer').classList.add('hidden');
        document.getElementById('securityActionBtn').style.display = 'flex';
        openModal('securityModal');
    }
    
    window.submitUpload = async function() {
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName').value;
        const password = document.getElementById('uploadPassword').value;
        const btn = document.getElementById('uploadBtn');

        if (!fileInput.files.length) return alert("Select a file");
        if (!fileName) return alert("Enter a file name");
        if (!password) return alert("Master password is required for encryption");

        const file = fileInput.files[0];
        
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Encrypting...';
        btn.disabled = true;

        try {
            const arrayBuffer = await file.arrayBuffer();
            const masterKey = await CryptoManager.deriveMasterKey(password);
            const { ciphertext, iv, wrappedDataKey, fileHash } = await CryptoManager.encryptFile(arrayBuffer, masterKey);

            const formData = new FormData();
            formData.append('originalName', fileName);
            formData.append('mimeType', file.type || 'application/octet-stream');
            formData.append('size', file.size);
            formData.append('fileHash', fileHash);
            formData.append('iv', iv);
            formData.append('wrappedDataKey', wrappedDataKey);
            formData.append('ciphertext', ciphertext);

            btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Uploading...';
            
            const response = await fetch('api/upload.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                alert("Upload successful!");
                window.location.reload();
            } else {
                throw new Error(result.error);
            }
        } catch (e) {
            alert("Error: " + e.message);
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    window.submitSecurityAction = async function() {
        const pwd = document.getElementById('masterPassword').value;
        const action = document.getElementById('securityActionType').value;
        const fileId = document.getElementById('securityFileId').value;
        const fileName = document.getElementById('securityTargetFile').textContent;
        
        if(!pwd) return alert("Please enter your password");
        
        const btn = document.querySelector('#securityModal button[type="button"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Processing...';
        btn.disabled = true;
        
        try {
            if (action === 'delete') {
                const response = await fetch('api/delete.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: fileId})
                });
                const res = await response.json();
                if (res.success) {
                    alert("Deleted!");
                    window.location.reload();
                } else throw new Error(res.error);
            } else if (action === 'download') {
                btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Fetching...';
                const response = await fetch('api/download.php?id=' + fileId);
                if (!response.ok) throw new Error("Failed to download encrypted file");
                
                const iv = response.headers.get('X-FMSS-IV');
                const wrappedKey = response.headers.get('X-FMSS-Key');
                
                const ciphertextBuffer = await response.arrayBuffer();
                
                btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Decrypting...';
                
                const masterKey = await CryptoManager.deriveMasterKey(pwd);
                const plaintextBuffer = await CryptoManager.decryptFile(ciphertextBuffer, iv, wrappedKey, masterKey);
                
                // Trigger download
                const blob = new Blob([plaintextBuffer], {type: "application/octet-stream"});
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                closeModal('securityModal');
            } else if (action === 'edit') {
                closeModal('securityModal'); // Prompt gets blocked if modal is acting weirdly sometimes, but prompt is okay.
                const newName = prompt("Enter new file name:", fileName);
                if (!newName || newName === fileName) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }
                
                btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Renaming...';
                const response = await fetch('api/file_rename.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: fileId, new_name: newName})
                });
                const res = await response.json();
                if (res.success) {
                    alert("File renamed successfully!");
                    window.location.reload();
                } else throw new Error(res.error);
            } else if (action === 'share') {
                const maxDownloads = prompt("Enter maximum downloads for this link:", "1");
                if (!maxDownloads) return;

                btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Preparing link...';
                
                // Fetch the wrapped key
                const headerResponse = await fetch('api/download.php?id=' + fileId);
                if (!headerResponse.ok) throw new Error("Failed to access file for sharing");
                
                const wrappedKeyCombined = headerResponse.headers.get('X-FMSS-Key');
                const masterKey = await CryptoManager.deriveMasterKey(pwd);
                
                const parts = wrappedKeyCombined.split(':');
                let dataKey;
                if (parts.length === 2) {
                    dataKey = await CryptoManager.unwrapKey(parts[1], parts[0], masterKey);
                } else {
                    dataKey = await CryptoManager.unwrapKey(wrappedKeyCombined, "000000000000000000000000", masterKey);
                }
                
                const rawKeyHex = await CryptoManager.exportKeyToHex(dataKey);
                
                const response = await fetch('api/share_create.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({file_id: fileId, max_downloads: maxDownloads})
                });
                const res = await response.json();
                if (res.success) {
                    const shareUrl = window.location.origin + window.location.pathname.replace('app/files.php', '') + 'share.php?id=' + res.share_id + '#' + rawKeyHex;
                    document.getElementById('shareLinkContainer').classList.remove('hidden');
                    document.getElementById('shareLinkResult').value = shareUrl;
                    document.getElementById('securityActionBtn').style.display = 'none';
                    alert("Share link generated securely!");
                } else throw new Error(res.error);
            }
        } catch (e) {
            alert("Action failed: " + e.message);
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>

<script type="module">
    import { animate, stagger } from "https://cdn.jsdelivr.net/npm/motion@11.11.13/+esm";
    if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches){
        animate(".animate-item",{opacity:[0,1],y:[20,0]},{delay:stagger(0.05,{startDelay:0.1}),duration:0.6,easing:[0.22,1,0.36,1]});
    }
</script>
</body>
</html>