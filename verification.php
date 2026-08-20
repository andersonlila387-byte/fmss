<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Device | FMSS Vault</title>
    
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

        /* Input number hidden arrows */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
    </style>
</head>
<body class="antialiased overflow-x-hidden selection:bg-green-500/30 selection:text-green-50">

    <div class="flex min-h-screen w-full">
        
        <!-- Left Side: Verification Form -->
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
                <div class="w-full max-w-sm form-container">
                    
                    <div class="mb-8">
                        <h1 class="text-2xl md:text-3xl font-bold mb-2 text-gradient tracking-tight">Verify Device</h1>
                        <p class="text-zinc-400 text-sm font-light">Enter the 6-digit confirmation code sent to your Telegram to activate your vault.</p>
                    </div>

                    <form id="verifyForm" class="space-y-6">
                        
                        <!-- OTP Input -->
                        <div class="space-y-1.5 animate-item">
                            <div class="relative">
                                <input type="number" id="code" name="code" required maxlength="6"
                                    class="w-full py-4 bg-white/5 border border-white/10 rounded-xl text-center text-3xl tracking-[1em] text-white placeholder-zinc-700 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all font-mono"
                                    placeholder="000000"
                                    oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2 animate-item">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-green-500 text-black text-sm font-bold hover:bg-green-400 hover:shadow-[0_0_20px_rgba(34,197,94,0.3)] transition-all flex items-center justify-center gap-2 group">
                                Verify & Activate
                                <i class="ph ph-check-circle font-bold group-hover:scale-110 transition-transform"></i>
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 text-center animate-item">
                        <p class="text-xs text-zinc-500">
                            Didn't receive the code? 
                            <button type="button" id="resend-btn" class="text-white hover:text-green-400 font-medium transition-colors border-b border-white/20 hover:border-green-400/50 pb-0.5 ml-1">Resend it</button>
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
                <span class="text-xs font-mono tracking-widest uppercase">Awaiting Verification</span>
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

        // Get User ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id');

        if (!userId) {
            UI.showAlert({ title: 'Invalid Session', message: "Please register again.", type: 'danger' }).then(() => {
                window.location.href = 'register.php';
            });
        }

        // Form Submission Logic
        const verifyForm = document.getElementById('verifyForm');
        
        verifyForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = verifyForm.querySelector('button[type="submit"]');
            const code = document.getElementById('code').value;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-xl"></i> Verifying...';
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');

            try {
                const response = await fetch('verify.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, code: code })
                });
                const result = await response.json();

                if (result.success) {
                    submitBtn.innerHTML = '<i class="ph ph-check-circle text-xl"></i> Verified!';
                    submitBtn.classList.replace('bg-green-500', 'bg-green-400');
                    UI.showToast('Device verified successfully!', 'success');
                    
                    // Proceed to login page after brief delay
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1000);
                } else {
                    throw new Error(result.error || 'Verification failed.');
                }
            } catch (error) {
                UI.showToast(error.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Verify & Activate <i class="ph ph-check-circle font-bold group-hover:scale-110 transition-transform"></i>';
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        });

        // Resend Code Logic
        const resendBtn = document.getElementById('resend-btn');
        let resendCooldown = false;

        resendBtn.addEventListener('click', async () => {
            if (resendCooldown) return;
            
            const originalText = resendBtn.innerText;
            resendBtn.innerHTML = '<i class="ph ph-spinner-gap animate-spin"></i> Sending...';
            resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
            resendCooldown = true;

            try {
                const response = await fetch('resend_code.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                });
                const result = await response.json();

                if (result.success) {
                    UI.showToast(result.message, 'success');
                    resendBtn.innerHTML = '<i class="ph ph-check text-green-400"></i> Sent!';
                    
                    let timeLeft = 60;
                    const countdown = setInterval(() => {
                        timeLeft--;
                        resendBtn.innerHTML = `Wait ${timeLeft}s`;
                        if (timeLeft <= 0) {
                            clearInterval(countdown);
                            resendBtn.innerHTML = originalText;
                            resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            resendCooldown = false;
                        }
                    }, 1000);
                } else {
                    throw new Error(result.error || 'Failed to resend code.');
                }
            } catch (error) {
                UI.showToast(error.message, 'error');
                resendBtn.innerHTML = originalText;
                resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                resendCooldown = false;
            }
        });
    </script>
</body>
</html>