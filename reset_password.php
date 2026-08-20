<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | FMSS Vault</title>
    
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
    </style>
</head>
<body class="antialiased overflow-x-hidden selection:bg-green-500/30 selection:text-green-50">

    <div class="flex min-h-screen w-full">
        
        <!-- Left Side: Form -->
        <div class="w-full lg:w-1/2 flex flex-col relative z-10 glass-panel">
            
            <!-- Abstract Background Glow -->
            <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-green-900/20 via-black to-black -z-10"></div>

            <!-- Header / Logo -->
            <div class="p-6 md:p-8 animate-fade-in">
                <a href="index.php" class="inline-flex items-center gap-2 group">
                    <i class="ph ph-shield-check text-2xl text-green-500 group-hover:text-green-400 transition-colors"></i>
                    <span class="font-mono font-bold text-lg tracking-widest text-white">FMSS<span class="text-green-500">.</span></span>
                </a>
            </div>

            <!-- Form Container -->
            <div class="flex-1 flex items-center justify-center p-6 md:p-8">
                <div class="w-full max-w-sm form-container -mt-16 md:mt-0">
                    
                    <div class="mb-8">
                        <h1 class="text-2xl md:text-3xl font-bold mb-2 text-gradient tracking-tight">Reset Password</h1>
                        <p class="text-zinc-400 text-sm font-light">Enter the recovery code sent to your Telegram and choose a new master password.</p>
                    </div>

                    <div class="mb-6 p-4 rounded-xl border border-red-500/30 bg-red-500/10 text-xs text-red-200 leading-relaxed animate-item">
                        <strong class="text-red-400 block mb-1">Zero-Knowledge Warning:</strong>
                        Resetting your master password restores access to your account, but you will lose access to previously encrypted files unless you have your Recovery Kit.
                    </div>

                    <form id="resetForm" class="space-y-4">
                        <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                        
                        <!-- OTP Input -->
                        <div class="space-y-1.5 animate-item">
                            <label for="otp" class="text-xs font-medium text-zinc-400 ml-1">Recovery Code</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="ph ph-key text-zinc-500"></i>
                                </div>
                                <input type="number" id="otp" name="otp" required maxlength="6"
                                    class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm tracking-widest text-white placeholder-zinc-600 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all font-mono"
                                    placeholder="000000"
                                    oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="space-y-1.5 animate-item">
                            <label for="password" class="text-xs font-medium text-zinc-400 ml-1">New Master Password</label>
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

                        <!-- Submit Button -->
                        <div class="pt-4 animate-item">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-400 hover:shadow-[0_0_20px_rgba(239,68,68,0.3)] transition-all flex items-center justify-center gap-2 group">
                                Proceed with Reset
                                <i class="ph ph-warning-circle font-bold group-hover:scale-110 transition-transform"></i>
                            </button>
                        </div>

                    </form>

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

        const resetForm = document.getElementById('resetForm');
        
        resetForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = resetForm.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-xl"></i> Resetting...';
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');

            const data = Object.fromEntries(new FormData(resetForm).entries());

            try {
                const response = await fetch('reset_password_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: data.email, otp: data.otp, new_password: data.password })
                });
                const result = await response.json();

                if (result.success) {
                    UI.showToast(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    throw new Error(result.error || 'Reset failed.');
                }
            } catch (error) {
                UI.showAlert({ title: 'Reset Failed', message: error.message, type: 'danger' });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        });
    </script>
</body>
</html>