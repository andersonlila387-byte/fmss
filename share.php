<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Share | FMSS Vault</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['Outfit', 'sans-serif'], mono: ['Space Grotesk', 'monospace'] } } }
        }
    </script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background-color: #000000; color: #ffffff; }
        .glass-panel { background: rgba(10, 10, 10, 0.6); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Background Glow -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-green-900/20 via-black to-black -z-10"></div>

    <div class="w-full max-w-md glass-panel border border-white/10 rounded-3xl p-8 relative shadow-2xl">
        <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-24 h-24 rounded-full bg-black border border-white/10 flex items-center justify-center z-10 shadow-[0_0_30px_rgba(34,197,94,0.2)]">
            <i class="ph ph-shield-check text-4xl text-green-400"></i>
        </div>

        <div class="mt-8 text-center">
            <h1 class="text-2xl font-bold mb-2">Secure Incoming File</h1>
            <p class="text-sm text-zinc-400 font-light mb-8">This file is End-to-End Encrypted. It will be decrypted locally on your device. The server cannot read it.</p>

            <div id="statusArea" class="glass-card bg-white/5 rounded-2xl p-5 mb-8 border border-white/5 text-left">
                <div class="flex items-center gap-3 text-zinc-400 mb-3" id="statusIcon">
                    <i class="ph ph-lock-key text-xl"></i>
                    <span class="text-sm font-medium">Ready to Decrypt</span>
                </div>
                <div class="w-full bg-black/50 rounded-full h-1.5 overflow-hidden">
                    <div id="progressBar" class="bg-green-500 h-full w-0 transition-all duration-300"></div>
                </div>
            </div>

            <button id="downloadBtn" onclick="startDecryption()" class="w-full py-4 bg-green-500 hover:bg-green-400 text-black font-bold rounded-xl transition-colors shadow-[0_4px_20px_rgba(34,197,94,0.3)] flex items-center justify-center gap-2 group">
                <i class="ph ph-download-simple text-lg group-hover:-translate-y-1 transition-transform"></i>
                Decrypt & Download
            </button>
        </div>
    </div>

    <script type="module">
        import { CryptoManager } from './assets/js/crypto.js';

        window.startDecryption = async function() {
            const btn = document.getElementById('downloadBtn');
            const progress = document.getElementById('progressBar');
            const statusText = document.querySelector('#statusIcon span');
            const statusIcon = document.querySelector('#statusIcon i');
            
            const urlParams = new URLSearchParams(window.location.search);
            const shareId = urlParams.get('id');
            const rawKeyHex = window.location.hash.substring(1);

            if (!shareId) return alert("Invalid link: Missing Share ID.");
            if (!rawKeyHex) return alert("Invalid link: Missing Decryption Key in URL.");

            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btn.innerHTML = '<i class="ph ph-spinner animate-spin text-lg"></i> Processing...';
            
            try {
                // 1. Fetch encrypted blob
                statusText.textContent = "Fetching encrypted file...";
                statusIcon.className = "ph ph-cloud-arrow-down text-xl";
                progress.style.width = "30%";
                
                const response = await fetch('app/api/share_download.php?id=' + shareId);
                if (!response.ok) {
                    const errorMsg = await response.json().catch(() => ({error: "Unknown error"}));
                    throw new Error(errorMsg.error || "Failed to download. Link may be expired or burned.");
                }

                const ivHex = response.headers.get('X-FMSS-IV');
                const rawName = response.headers.get('X-FMSS-Name');
                const fileName = rawName ? decodeURIComponent(rawName) : "Shared_Decrypted_File";
                
                // We do not get X-FMSS-Key because it's a share link. The key is in the hash!
                
                const ciphertextBuffer = await response.arrayBuffer();
                progress.style.width = "60%";

                // 2. Decrypt locally
                statusText.textContent = "Decrypting locally (Zero-Knowledge)...";
                statusIcon.className = "ph ph-cpu text-xl text-green-400";
                
                // Import the raw key from the URL hash
                const dataKey = await CryptoManager.importKeyFromHex(rawKeyHex);
                const ivBuffer = CryptoManager.hexToBuffer(ivHex);
                
                const plaintextBuffer = await window.crypto.subtle.decrypt(
                    { name: "AES-GCM", iv: ivBuffer },
                    dataKey,
                    ciphertextBuffer
                );
                progress.style.width = "90%";

                // 3. Trigger Download
                statusText.textContent = "Decryption successful!";
                statusIcon.className = "ph ph-check-circle text-xl text-green-500";
                progress.style.width = "100%";
                
                const blob = new Blob([plaintextBuffer], {type: "application/octet-stream"});
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName; 
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                
                btn.innerHTML = 'Download Complete';
                
            } catch (e) {
                statusText.textContent = "Error: " + e.message;
                statusText.classList.add('text-red-400');
                statusIcon.className = "ph ph-warning-circle text-xl text-red-400";
                progress.style.width = "0%";
                progress.classList.remove('bg-green-500');
                progress.classList.add('bg-red-500');
                btn.innerHTML = 'Decryption Failed';
            }
        };
    </script>
</body>
</html>
