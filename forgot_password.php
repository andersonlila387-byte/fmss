<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Vault | FMSS</title>
    
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
                        <h1 class="text-2xl md:text-3xl font-bold mb-2 text-gradient tracking-tight">Recover Vault</h1>
                        <p class="text-zinc-400 text-sm font-light">Enter your email to receive a recovery code via Telegram.</p>
                    </div>

                    <form id="forgotForm" class="space-y-4">
                        
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

                        <!-- Submit Button -->
                        <div class="pt-2 animate-item">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-white text-black text-sm font-bold hover:bg-zinc-200 transition-all flex items-center justify-center gap-2 group">
                                Request Recovery Code
                                <i class="ph ph-paper-plane-tilt font-bold group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>

                    </form>

                    <div class="mt-8 text-center animate-item">
                        <p class="text-xs text-zinc-500">
                            Remember your password? 
                            <a href="login.php" class="text-white hover:text-green-400 font-medium transition-colors border-b border-white/20 hover:border-green-400/50 pb-0.5 ml-1">Sign in</a>
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

        animate(
            ".animate-item",
            { opacity: [0, 1], y: [20, 0] },
            { delay: stagger(0.1, { startDelay: 0.2 }), duration: 0.6, easing: [0.22, 1, 0.36, 1] }
        );

        const forgotForm = document.getElementById('forgotForm');
        
        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = forgotForm.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-xl"></i> Sending...';
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');

            const email = document.getElementById('email').value;

            try {
                const response = await fetch('forgot_password_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const result = await response.json();

                if (result.success) {
                    UI.showToast(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'reset_password.php?email=' + encodeURIComponent(email);
                    }, 1500);
                } else {
                    throw new Error(result.error || 'Request failed.');
                }
            } catch (error) {
                UI.showAlert({ title: 'Request Failed', message: error.message, type: 'danger' });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalContent;
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        });
    </script>
</body>
</html>