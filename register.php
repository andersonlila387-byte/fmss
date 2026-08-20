<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Vault | FMSS</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        mono: ['Space Grotesk', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Spline 3D Viewer -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.32/build/spline-viewer.js"></script>
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        body {
            background-color: #000000;
            color: #ffffff;
        }
        
        /* Premium Glassmorphism */
        .glass-panel {
            background: rgba(10, 10, 10, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Gradient Text */
        .text-gradient {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(to bottom, #ffffff, #a1a1aa);
        }

        /* Custom Autofill Styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #0a0a0a inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>
<body class="antialiased overflow-x-hidden selection:bg-green-500/30 selection:text-green-50">

    <div class="flex min-h-screen w-full">
        
        <!-- Left Side: Registration Form -->
        <div class="w-full lg:w-1/2 flex flex-col relative z-10 glass-panel">
            
            <!-- Abstract Background Glow -->
            <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-green-900/20 via-black to-black -z-10"></div>

            <!-- Header / Logo -->
            <div class="p-6 md:p-8 animate-fade-in flex justify-between items-center">
                <a href="index.php" class="inline-flex items-center gap-2 group">
                    <i class="ph ph-shield-check text-2xl text-green-500 group-hover:text-green-400 transition-colors"></i>
                    <span class="font-mono font-bold text-lg tracking-widest text-white">FMSS<span class="text-green-500">.</span></span>
                </a>
                <div class="text-xs font-mono text-zinc-500 flex items-center gap-1.5 border border-white/5 bg-white/5 px-3 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    Zero-Knowledge
                </div>
            </div>

            <!-- Form Container -->
            <div class="flex-1 flex items-center justify-center p-6 md:p-8">
                <div class="w-full max-w-sm form-container -mt-16 md:mt-0">
                    
                    <div class="mb-8">
                        <h1 class="text-2xl md:text-3xl font-bold mb-2 text-gradient tracking-tight">Create Vault</h1>
                        <p class="text-zinc-400 text-sm font-light">Generate your cryptographic keys and secure your files.</p>
                    </div>

                    <form id="registerForm">
                        
                        <!-- Step 1 -->
                        <div id="step-1" class="space-y-4">
                            <!-- Username Input -->
                            <div class="space-y-1.5 animate-item">
                                <label for="username" class="text-xs font-medium text-zinc-400 ml-1">Username</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <i class="ph ph-user text-zinc-500"></i>
                                    </div>
                                    <input type="text" id="username" name="username" required 
                                        class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-zinc-600 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all"
                                        placeholder="your_alias">
                                </div>
                        </div>

                        <!-- Email Input -->
                        <div class="space-y-1.5 animate-item">
                            <label for="email" class="text-xs font-medium text-zinc-400 ml-1">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="ph ph-envelope-simple text-zinc-500"></i>
                                </div>
                                <input type="email" id="email" name="email" required 
                                    class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-zinc-600 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all"
                                    placeholder="name@company.com">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="space-y-1.5 animate-item">
                            <label for="password" class="text-xs font-medium text-zinc-400 ml-1">Master Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="ph ph-lock-key text-zinc-500"></i>
                                </div>
                                <input type="password" id="password" name="password" required 
                                    class="w-full pl-10 pr-10 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-zinc-600 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all"
                                    placeholder="••••••••••••">
                                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-zinc-500 hover:text-zinc-300 transition-colors">
                                    <i class="ph ph-eye" id="eye-icon"></i>
                                </button>
                            </div>
                            
                            <!-- Password Strength Indicator -->
                            <div class="pt-1">
                                <div class="flex items-center justify-between text-[10px] uppercase tracking-wider font-mono mb-1">
                                    <span class="text-zinc-500">Encryption Strength</span>
                                    <span id="strength-text" class="text-zinc-500 transition-colors">-</span>
                                </div>
                                <div class="flex gap-1 h-1">
                                    <div id="str-1" class="flex-1 rounded-full bg-white/10 transition-colors duration-300"></div>
                                    <div id="str-2" class="flex-1 rounded-full bg-white/10 transition-colors duration-300"></div>
                                    <div id="str-3" class="flex-1 rounded-full bg-white/10 transition-colors duration-300"></div>
                                    <div id="str-4" class="flex-1 rounded-full bg-white/10 transition-colors duration-300"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Continue Button -->
                        <div class="pt-4 animate-item">
                            <button type="button" id="next-step-btn" class="w-full py-2.5 rounded-xl bg-white text-black text-sm font-bold hover:bg-zinc-200 transition-all flex items-center justify-center gap-2 group">
                                Continue
                                <i class="ph ph-arrow-right font-bold group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                    </div>
                        
                        <!-- Step 2 -->
                        <div id="step-2" class="space-y-4 hidden">
                            
                            <!-- Telegram Connection Flow -->
                            <div class="p-6 rounded-2xl glass-card text-center border border-white/10 relative overflow-hidden group animate-item">
                                <div class="absolute inset-0 bg-blue-500/5 group-hover:bg-blue-500/10 transition-colors pointer-events-none"></div>
                                
                                <div class="w-16 h-16 mx-auto bg-blue-500/20 rounded-full flex items-center justify-center mb-4 border border-blue-500/30">
                                    <i class="ph ph-telegram-logo text-3xl text-blue-400"></i>
                                </div>
                                
                                <h3 class="text-white font-medium text-lg mb-2">Connect Telegram</h3>
                                <p class="text-zinc-400 text-sm mb-6 max-w-[250px] mx-auto leading-relaxed">
                                    Click below to open Telegram and start the bot. We will securely link your account automatically.
                                </p>
                                
                                <a href="#" id="telegram-connect-btn" target="_blank" class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-[0_0_20px_rgba(59,130,246,0.3)] hover:scale-105 active:scale-95">
                                    <span>Open Telegram</span>
                                    <i class="ph ph-arrow-up-right font-bold"></i>
                                </a>
                                
                                <!-- Connection Status -->
                                <div id="telegram-status" class="mt-6 flex flex-col items-center justify-center gap-2 hidden">
                                    <i class="ph ph-spinner-gap animate-spin text-2xl text-blue-400"></i>
                                    <span class="text-xs font-mono text-zinc-400">Waiting for connection...</span>
                                </div>
                                <div id="telegram-success" class="mt-6 flex flex-col items-center justify-center gap-2 hidden">
                                    <i class="ph-fill ph-check-circle text-2xl text-green-500"></i>
                                    <span class="text-xs font-mono text-green-400">Successfully connected!</span>
                                </div>
                            </div>

                            <!-- Hidden Chat ID -->
                            <input type="hidden" id="telegram_chat_id" name="telegram_chat_id" required>

                            <!-- Submit Button -->
                            <div class="pt-2 animate-item hidden" id="final-submit-container">
                                <button type="submit" id="register-submit-btn" class="w-full py-2.5 rounded-xl bg-green-500 text-black text-sm font-bold hover:bg-green-400 hover:shadow-[0_0_20px_rgba(34,197,94,0.3)] transition-all flex items-center justify-center gap-2 group">
                                    Finalizing Registration...
                                    <i class="ph ph-spinner-gap animate-spin font-bold"></i>
                                </button>
                                <p class="text-[10px] text-center text-zinc-500 mt-3 leading-relaxed">
                                    By creating a vault, you acknowledge that FMSS cannot recover your data if you lose your master password.
                                </p>
                            </div>
                            
                            <!-- Back Button -->
                            <div class="text-center animate-item mt-2">
                                <button type="button" id="prev-step-btn" class="text-xs text-zinc-500 hover:text-white transition-colors flex items-center justify-center gap-1 mx-auto">
                                    <i class="ph ph-arrow-left"></i> Back to account details
                                </button>
                            </div>
                        </div>

                    </form>

                    <div class="mt-6 text-center animate-item">
                        <p class="text-xs text-zinc-500">
                            Already have a vault? 
                            <a href="login.php" class="text-white hover:text-green-400 font-medium transition-colors border-b border-white/20 hover:border-green-400/50 pb-0.5 ml-1">Access it</a>
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Right Side: 3D Animation (Hidden on Mobile) -->
        <div class="hidden lg:flex w-1/2 bg-zinc-950 relative items-center justify-center overflow-hidden border-l border-white/5">
            
            <!-- Fallback loader -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-zinc-600 z-0">
                <i class="ph ph-circle-notch animate-spin text-3xl mb-2 text-green-500/50"></i>
                <span class="text-xs font-mono tracking-widest uppercase">Initializing Canvas</span>
            </div>

            <!-- Spline Viewer -->
            <spline-viewer 
                class="w-full h-full relative z-10 scale-[1.2]" 
                style="filter: hue-rotate(-70deg) saturate(1.2);"
                url="https://prod.spline.design/kZDDjO5HuC9GJUM2/scene.splinecode"
                events-target="global"
            ></spline-viewer>
            
            <!-- Overlay gradient to blend edges -->
            <div class="absolute inset-0 bg-gradient-to-r from-zinc-950 via-transparent to-transparent pointer-events-none z-20"></div>
        </div>

    </div>

    <!-- Motion.dev & Interactions Script -->
    <script type="module">
        import { animate, stagger } from "https://cdn.jsdelivr.net/npm/motion@11.11.13/+esm";
        import { UI } from "./assets/js/ui.js";

        // Stagger entrance animation for form elements
        animate(
            ".animate-item",
            { opacity: [0, 1], y: [20, 0] },
            { delay: stagger(0.1, { startDelay: 0.2 }), duration: 0.6, easing: [0.22, 1, 0.36, 1] }
        );

        // Password Toggle Logic
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');

        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.className = isPassword ? 'ph ph-eye-slash' : 'ph ph-eye';
        });

        // Password Strength Logic
        const str1 = document.getElementById('str-1');
        const str2 = document.getElementById('str-2');
        const str3 = document.getElementById('str-3');
        const str4 = document.getElementById('str-4');
        const strText = document.getElementById('strength-text');
        const bars = [str1, str2, str3, str4];

        passwordInput.addEventListener('input', (e) => {
            const val = e.target.value;
            let score = 0;
            
            if (val.length > 0) {
                if (val.length >= 8) score++;
                if (val.match(/[a-z]/) && val.match(/[A-Z]/)) score++;
                if (val.match(/\d/)) score++;
                if (val.match(/[^a-zA-Z\d]/)) score++;
            }

            // Reset classes
            bars.forEach(b => b.className = 'flex-1 rounded-full bg-white/10 transition-colors duration-300');
            
            if (val.length === 0) {
                strText.textContent = '-';
                strText.className = 'text-zinc-500 transition-colors';
            } else if (score <= 1) {
                str1.classList.replace('bg-white/10', 'bg-red-500');
                strText.textContent = 'Weak';
                strText.className = 'text-red-500 transition-colors';
            } else if (score === 2) {
                str1.classList.replace('bg-white/10', 'bg-orange-500');
                str2.classList.replace('bg-white/10', 'bg-orange-500');
                strText.textContent = 'Fair';
                strText.className = 'text-orange-500 transition-colors';
            } else if (score === 3) {
                str1.classList.replace('bg-white/10', 'bg-yellow-400');
                str2.classList.replace('bg-white/10', 'bg-yellow-400');
                str3.classList.replace('bg-white/10', 'bg-yellow-400');
                strText.textContent = 'Good';
                strText.className = 'text-yellow-400 transition-colors';
            } else {
                bars.forEach(b => b.classList.replace('bg-white/10', 'bg-green-500'));
                strText.textContent = 'Strong';
                strText.className = 'text-green-500 transition-colors';
            }
        });

        // Step Navigation Logic
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
        const nextBtn = document.getElementById('next-step-btn');
        const prevBtn = document.getElementById('prev-step-btn');
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const pwdInput = document.getElementById('password');

        // Automation vars
        const connectBtn = document.getElementById('telegram-connect-btn');
        const statusDiv = document.getElementById('telegram-status');
        const successDiv = document.getElementById('telegram-success');
        const chatIdInput = document.getElementById('telegram_chat_id');
        const finalSubmitContainer = document.getElementById('final-submit-container');
        let pollingInterval = null;
        let connectionToken = '';

        nextBtn.addEventListener('click', () => {
            // Validate Step 1
            if (!usernameInput.checkValidity()) { usernameInput.reportValidity(); return; }
            if (!emailInput.checkValidity()) { emailInput.reportValidity(); return; }
            if (!pwdInput.checkValidity()) { pwdInput.reportValidity(); return; }

            // Generate unique start token and update link
            connectionToken = crypto.randomUUID().replace(/-/g, '');
            connectBtn.href = `https://t.me/smartfmss_bot?start=${connectionToken}`;

            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            
            animate(
                "#step-2 .animate-item",
                { opacity: [0, 1], y: [20, 0] },
                { delay: stagger(0.1), duration: 0.5, easing: [0.22, 1, 0.36, 1] }
            );
        });

        prevBtn.addEventListener('click', () => {
            step2.classList.add('hidden');
            step1.classList.remove('hidden');
            
            animate(
                "#step-1 .animate-item",
                { opacity: [0, 1], y: [-20, 0] },
                { delay: stagger(0.1), duration: 0.5, easing: [0.22, 1, 0.36, 1] }
            );
        });

        // Polling Logic
        connectBtn.addEventListener('click', () => {
            connectBtn.classList.add('hidden');
            statusDiv.classList.remove('hidden');
            
            if (!pollingInterval) {
                pollingInterval = setInterval(checkConnection, 3000);
            }
        });

        async function checkConnection() {
            try {
                const response = await fetch(`check_telegram_connection.php?token=${connectionToken}`);
                const result = await response.json();
                
                if (result.success && result.connected) {
                    // Stop polling
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                    
                    // Embed the ID and show success
                    chatIdInput.value = result.chat_id;
                    statusDiv.classList.add('hidden');
                    successDiv.classList.remove('hidden');
                    finalSubmitContainer.classList.remove('hidden');
                    
                    // Auto submit form
                    setTimeout(() => {
                        document.getElementById('register-submit-btn').click();
                    }, 1200);
                }
            } catch (error) {
                console.error("Polling error:", error);
            }
        }

        // Form Submission Logic
        const registerForm = document.getElementById('registerForm');
        
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            
            // UI Loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-xl"></i> Generating Keys...';
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');

            // Gather data
            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData.entries());

            // Generate Mock Keys (To be replaced with real WebCrypto API logic later)
            data.public_key = 'pub_' + crypto.randomUUID();
            data.encrypted_private_key = 'priv_' + crypto.randomUUID();

            try {
                const response = await fetch('process_register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    UI.showToast('Vault created successfully!', 'success');
                    window.location.href = 'verification.php?user_id=' + encodeURIComponent(result.user_id);
                } else {
                    throw new Error(result.error || 'Unknown error occurred.');
                }
            } catch (error) {
                UI.showAlert({ title: 'Registration Failed', message: error.message, type: 'danger' });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        });
    </script>
</body>
</html>