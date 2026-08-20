<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FMSS | Elite Information Security</title>
    
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
                    },
                    backgroundImage: {
                        'glass': 'linear-gradient(to bottom right, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.01))',
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
        
        /* Premium Glassmorphism - Adjusted for Black */
        .glass-card {
            background: rgba(10, 10, 10, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        }

        /* Animated Spotlight Overlay - Green */
        #spotlight-overlay {
            pointer-events: none;
            position: absolute;
            inset: 0;
            z-index: 1;
            background: radial-gradient(circle 600px at var(--x, 50%) var(--y, 50%), rgba(34, 197, 94, 0.08), transparent 40%);
            transition: opacity 0.3s ease;
            opacity: 0;
        }

        .hero-container:hover #spotlight-overlay {
            opacity: 1;
        }

        /* Custom Scrollbar - Black Theme */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #000000; 
        }
        ::-webkit-scrollbar-thumb {
            background: #27272a; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #3f3f46; 
        }

        /* Gradient Text - White to Green */
        .text-gradient {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(to bottom, #ffffff, #a1a1aa);
        }
        .text-gradient-accent {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(to right, #22c55e, #ffffff);
        }

        /* FAQ Accordion */
        details > summary {
            list-style: none;
        }
        details > summary::-webkit-details-marker {
            display: none;
        }
    </style>
</head>
<body class="antialiased overflow-x-hidden selection:bg-green-500/30 selection:text-green-50 bg-black text-white">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 top-0 transition-all duration-300 glass-card border-b-0 border-white/5" id="navbar">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-400 to-green-700 p-[1px]">
                    <div class="w-full h-full bg-black rounded-[7px] flex items-center justify-center">
                        <i class="ph ph-shield-check text-2xl text-green-400"></i>
                    </div>
                </div>
                <span class="font-mono font-bold text-xl tracking-wider text-white">FMSS<span class="text-green-500">.</span></span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-zinc-400">
                <a href="#vision" class="hover:text-white transition-colors">Vision</a>
                <a href="#security" class="hover:text-white transition-colors">Security Model</a>
                <a href="#ledger" class="hover:text-white transition-colors">The Ledger</a>
                <a href="#pricing" class="hover:text-white transition-colors">Pricing</a>
                <a href="#faq" class="hover:text-white transition-colors">FAQ</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="login.php" class="text-sm font-medium text-zinc-400 hover:text-white transition-colors hidden sm:block">Sign In</a>
                <a href="login.php" class="hidden md:block relative group px-5 py-2 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 transition-all overflow-hidden">
                    <div class="absolute inset-0 w-0 bg-gradient-to-r from-green-500 to-white transition-all duration-300 ease-out group-hover:w-full opacity-20"></div>
                    <span class="relative text-xs font-medium text-white">Access Vault</span>
                </a>
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-zinc-300 hover:text-green-400 transition-colors p-1 z-[110] relative">
                    <i class="ph ph-list text-2xl"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar Menu -->
    <div id="mobile-menu" class="fixed inset-0 z-[100] hidden">
        <!-- Backdrop -->
        <div id="mobile-menu-backdrop" class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300 cursor-pointer"></div>
        
        <!-- Sidebar Panel -->
        <div id="mobile-sidebar" class="absolute inset-y-0 right-0 w-4/5 max-w-sm bg-zinc-950/95 backdrop-blur-xl border-l border-white/10 shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col">
            <!-- Header -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-white/5">
                <div class="flex items-center gap-2">
                    <i class="ph ph-shield-check text-2xl text-green-500"></i>
                    <span class="font-mono font-bold text-lg tracking-widest text-white">FMSS<span class="text-green-500">.</span></span>
                </div>
                <button id="close-menu-btn" class="text-zinc-400 hover:text-white transition-colors p-2 -mr-2">
                    <i class="ph ph-x text-2xl"></i>
                </button>
            </div>
            
            <!-- Links -->
            <div class="flex-1 overflow-y-auto py-6 px-4 flex flex-col gap-2">
                <a href="#vision" class="mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-zinc-300 hover:text-white hover:bg-white/5 transition-all">
                    <i class="ph ph-eye text-xl text-zinc-500"></i>
                    <span class="font-medium">Vision</span>
                </a>
                <a href="#security" class="mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-zinc-300 hover:text-white hover:bg-white/5 transition-all">
                    <i class="ph ph-lock-key text-xl text-zinc-500"></i>
                    <span class="font-medium">Security Model</span>
                </a>
                <a href="#ledger" class="mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-zinc-300 hover:text-white hover:bg-white/5 transition-all">
                    <i class="ph ph-tree-structure text-xl text-zinc-500"></i>
                    <span class="font-medium">The Ledger</span>
                </a>
                <a href="#pricing" class="mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-zinc-300 hover:text-white hover:bg-white/5 transition-all">
                    <i class="ph ph-currency-circle-dollar text-xl text-zinc-500"></i>
                    <span class="font-medium">Pricing</span>
                </a>
                <a href="#faq" class="mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-zinc-300 hover:text-white hover:bg-white/5 transition-all">
                    <i class="ph ph-question text-xl text-zinc-500"></i>
                    <span class="font-medium">FAQ</span>
                </a>
            </div>
            
            <!-- Footer / CTA -->
            <div class="p-6 border-t border-white/5 bg-black/50">
                <a href="login.php" class="mobile-link flex items-center justify-center w-full py-3 rounded-xl bg-green-500 text-black font-bold hover:bg-green-400 hover:scale-[1.02] transition-all">
                    Sign In / Access Vault
                </a>
                <div class="mt-4 flex items-center justify-center gap-2 text-xs font-mono text-zinc-500">
                    <i class="ph ph-shield-check text-green-500"></i> E2E Encrypted Connection
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex flex-col justify-center pt-20 overflow-hidden hero-container" id="hero">
        <div id="spotlight-overlay"></div>
        
        <!-- Background elements -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-green-500/10 blur-[120px] rounded-full pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-6 w-full relative z-10 grid lg:grid-cols-2 gap-12 items-center">
            
            <!-- Left: Copy -->
            <div class="hero-content flex flex-col items-start gap-6 pt-12 lg:pt-0">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-green-500/30 bg-green-500/10 text-green-400 text-xs font-mono mb-4">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    AES-256-GCM Zero-Knowledge Enabled
                </div>
                
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-[1.1] tracking-tight">
                    <span class="text-gradient">Absolute Security.</span><br />
                    <span class="text-gradient">Uncompromised</span><br />
                    <span class="text-gradient-accent">Control.</span>
                </h1>
                
                <p class="text-base md:text-lg text-zinc-400 max-w-lg leading-relaxed font-light">
                    A cinematic vault that secures your most sensitive assets. Encrypted locally, permanently fingerprinted, and guarded by a tamper-evident ledger.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center gap-3 mt-4 w-full sm:w-auto">
                    <a href="register.php" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-green-500 text-black font-bold hover:bg-green-400 hover:scale-[1.02] transition-all flex items-center justify-center gap-2 text-sm md:text-base">
                        Create Secure Vault
                        <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                    <button class="w-full sm:w-auto px-6 py-3 rounded-xl glass-card text-white font-medium hover:bg-white/10 transition-colors flex items-center justify-center gap-2 text-sm md:text-base">
                        <i class="ph ph-play-circle text-xl text-green-400"></i>
                        View Architecture
                    </button>
                </div>
            </div>

            <!-- Right: 3D Spline Interactive Element -->
            <div class="relative h-[500px] lg:h-[700px] w-full hero-spline flex items-center justify-center">
                <!-- Fallback loader -->
                <div class="absolute inset-0 flex flex-col items-center justify-center text-zinc-500 z-0">
                    <i class="ph ph-spinner-gap animate-spin text-4xl mb-2 text-green-500"></i>
                    <span class="text-sm font-mono tracking-widest uppercase">Initializing Canvas</span>
                </div>
                <!-- Spline Web Component (with CSS hue-rotate filter to force it green!) -->
                <spline-viewer 
                    class="w-full h-full relative z-10" 
                    style="filter: hue-rotate(-70deg) saturate(1.2);"
                    url="https://prod.spline.design/kZDDjO5HuC9GJUM2/scene.splinecode"
                    events-target="global"
                ></spline-viewer>
            </div>
        </div>
    </section>

    <!-- Trust Banner -->
    <div class="border-y border-white/5 bg-zinc-950 backdrop-blur-sm relative z-20 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex flex-wrap justify-between items-center gap-8 opacity-70 font-mono text-sm uppercase tracking-widest text-zinc-400 stagger-banner">
                <div class="flex items-center gap-3 hover:text-green-400 transition-colors"><i class="ph ph-lock-key text-xl"></i> Zero-Knowledge</div>
                <div class="hidden md:block w-1 h-1 rounded-full bg-zinc-600"></div>
                <div class="flex items-center gap-3 hover:text-white transition-colors"><i class="ph ph-fingerprint text-xl"></i> SHA-256 Fingerprinting</div>
                <div class="hidden md:block w-1 h-1 rounded-full bg-zinc-600"></div>
                <div class="flex items-center gap-3 hover:text-green-400 transition-colors"><i class="ph ph-link text-xl"></i> Tamper-Evident Ledger</div>
                <div class="hidden lg:block w-1 h-1 rounded-full bg-zinc-600"></div>
                <div class="flex items-center gap-3 hover:text-white transition-colors"><i class="ph ph-share-network text-xl"></i> P2P Local Transfer</div>
            </div>
        </div>
    </div>

    <!-- Core Features Grid -->
    <section class="py-20 md:py-32 relative" id="security">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-20 section-header">
                <h2 class="text-2xl md:text-4xl font-bold mb-6 text-gradient">The Architecture of Trust</h2>
                <p class="text-zinc-400 text-base md:text-lg font-light">Your information never leaves your device unencrypted. We utilize blockchain-inspired cryptography to guarantee authenticity.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <!-- Card 1: Green Accent -->
                <div class="glass-card rounded-2xl p-8 hover:bg-white/[0.04] transition-colors feature-card group">
                    <div class="w-14 h-14 rounded-xl bg-green-500/10 flex items-center justify-center mb-8 border border-green-500/20 group-hover:scale-110 transition-transform">
                        <i class="ph ph-file-lock text-3xl text-green-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-white">Client-Side E2E</h3>
                    <p class="text-zinc-400 text-sm leading-relaxed mb-6">Files are encrypted directly in your browser using AES-256-GCM before upload. The server only ever stores ciphertext. We cannot read your data.</p>
                    <a href="#" class="text-green-400 font-mono text-xs uppercase tracking-wider flex items-center gap-2 group-hover:gap-3 transition-all">
                        Explore Encryption <i class="ph ph-arrow-right"></i>
                    </a>
                </div>

                <!-- Card 2: White Accent -->
                <div class="glass-card rounded-2xl p-8 hover:bg-white/[0.04] transition-colors feature-card group relative overflow-hidden">
                    <!-- Subtle glow -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 blur-[50px] pointer-events-none"></div>
                    
                    <div class="w-14 h-14 rounded-xl bg-white/10 flex items-center justify-center mb-8 border border-white/20 group-hover:scale-110 transition-transform">
                        <i class="ph ph-tree-structure text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-white">Hash-Chained Ledger</h3>
                    <p class="text-zinc-400 text-sm leading-relaxed mb-6">Every action is permanently recorded in an immutable, tamper-evident log. Altering past records breaks the chain, providing a provable audit trail.</p>
                    <a href="#" class="text-white font-mono text-xs uppercase tracking-wider flex items-center gap-2 group-hover:gap-3 transition-all">
                        View Audit System <i class="ph ph-arrow-right"></i>
                    </a>
                </div>

                <!-- Card 3: Deep Green / Black Accent -->
                <div class="glass-card rounded-2xl p-8 hover:bg-white/[0.04] transition-colors feature-card group">
                    <div class="w-14 h-14 rounded-xl bg-green-900/40 flex items-center justify-center mb-8 border border-green-700/50 group-hover:scale-110 transition-transform">
                        <i class="ph ph-signature text-3xl text-green-500"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-white">Verifiable Sharing</h3>
                    <p class="text-zinc-400 text-sm leading-relaxed mb-6">When sharing, your file's fingerprint is cryptographically signed via Ed25519. The recipient proves authenticity before opening.</p>
                    <a href="#" class="text-green-500 font-mono text-xs uppercase tracking-wider flex items-center gap-2 group-hover:gap-3 transition-all">
                        How Sharing Works <i class="ph ph-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Ledger Section -->
    <section class="py-24 relative overflow-hidden bg-zinc-950 border-t border-white/5" id="ledger">
        <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
            
            <div class="relative w-full aspect-square md:aspect-video lg:aspect-square glass-card rounded-2xl border border-white/10 p-6 flex flex-col justify-center overflow-hidden ledger-visual bg-black/50">
                <!-- Abstract visual of a hash chain -->
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')]"></div>
                
                <div class="relative z-10 flex flex-col font-mono text-xs sm:text-sm w-full max-w-[360px] mx-auto lg:mx-0">
                    <!-- Block 1 -->
                    <div class="bg-black/80 backdrop-blur-md border border-white/10 p-5 rounded-xl shadow-2xl transform md:translate-x-6 hover:-translate-y-1 transition-all duration-300 relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent rounded-xl pointer-events-none"></div>
                        <div class="flex justify-between items-center text-zinc-500 mb-3 text-[10px] sm:text-xs relative z-10">
                            <span class="flex items-center gap-1.5"><i class="ph ph-cube text-zinc-400"></i> Block #1042</span>
                            <span class="flex items-center gap-1.5"><i class="ph ph-clock text-zinc-400"></i> 12:04:22 UTC</span>
                        </div>
                        <div class="flex items-center gap-2 text-white font-medium mb-3 relative z-10">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse shadow-[0_0_8px_rgba(59,130,246,0.8)]"></span>
                            ACTION: UPLOAD
                        </div>
                        <div class="flex flex-col mb-1 leading-relaxed relative z-10">
                            <span class="text-zinc-600 text-[10px]">TARGET FILE:</span>
                            <span class="text-zinc-300 truncate">e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-white/5 flex flex-col leading-relaxed relative z-10">
                            <span class="text-zinc-600 text-[10px]">GENERATED HASH:</span>
                            <span class="text-green-400 truncate">9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08</span>
                        </div>
                    </div>

                    <!-- Chain Link -->
                    <div class="relative h-10 md:h-12 ml-8 md:ml-14 flex items-center">
                        <div class="absolute inset-y-0 left-0 w-[2px] bg-gradient-to-b from-white/10 via-green-500/50 to-green-500"></div>
                        <div class="absolute left-[-4px] top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full border border-green-500 bg-black"></div>
                    </div>

                    <!-- Block 2 -->
                    <div class="bg-black/90 backdrop-blur-md border border-green-500/40 p-5 rounded-xl shadow-[0_0_40px_rgba(34,197,94,0.15)] relative group hover:-translate-y-1 transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-transparent rounded-xl pointer-events-none"></div>
                        <div class="absolute -left-[5px] top-[40px] w-2 h-2 bg-green-400 rounded-full shadow-[0_0_12px_rgba(34,197,94,1)]"></div>
                        
                        <div class="flex justify-between items-center text-zinc-500 mb-3 text-[10px] sm:text-xs relative z-10">
                            <span class="flex items-center gap-1.5"><i class="ph ph-cube text-zinc-400"></i> Block #1043</span>
                            <span class="flex items-center gap-1.5"><i class="ph ph-clock text-zinc-400"></i> 14:21:05 UTC</span>
                        </div>
                        <div class="flex items-center gap-2 text-green-400 font-medium mb-3 relative z-10">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                            ACTION: SECURE_SHARE
                        </div>
                        <div class="flex flex-col mb-1 leading-relaxed relative z-10">
                            <span class="text-zinc-600 text-[10px]">PREVIOUS HASH:</span>
                            <span class="text-zinc-400 truncate">9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08</span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-green-500/20 flex flex-col leading-relaxed relative z-10">
                            <span class="text-green-600/80 text-[10px]">CRYPTOGRAPHIC SIGNATURE (Ed25519):</span>
                            <span class="text-white truncate">ed25519_a7f8b9e6c4d21f3a098b7e6d5c4b3a2f1e0d9c8b7a6f5e4d3c2b1a0f9e8d7c6b</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ledger-text">
                <h2 class="text-2xl md:text-3xl font-bold mb-6 text-gradient">Tamper-Proof by Design</h2>
                <p class="text-zinc-400 mb-8 leading-relaxed text-sm md:text-base">
                    Our database isn't just a table; it's a cryptographically linked ledger. Every time a file is uploaded, shared, or viewed, a new block is generated containing the hash of the previous state.
                </p>
                <ul class="space-y-4 mb-10">
                    <li class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-green-500/10 flex items-center justify-center shrink-0 mt-1 border border-green-500/20">
                            <i class="ph ph-check text-green-400"></i>
                        </div>
                        <div>
                            <h4 class="text-white font-medium">Immutable History</h4>
                            <p class="text-zinc-500 text-sm mt-1">You cannot alter any past record without breaking every block after it.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0 mt-1 border border-white/20">
                            <i class="ph ph-shield-check text-white"></i>
                        </div>
                        <div>
                            <h4 class="text-white font-medium">Cryptographic Proof</h4>
                            <p class="text-zinc-500 text-sm mt-1">Verify the entire chain at the click of a button to ensure zero unauthorized modifications.</p>
                        </div>
                    </li>
                </ul>
                <button class="px-6 py-3 rounded-lg border border-white/20 hover:bg-white/10 hover:text-green-400 transition-colors text-sm font-medium">
                    Read the Security Whitepaper
                </button>
            </div>
        </div>
    </section>

    <!-- 1. How It Works Section -->
    <section class="py-20 md:py-32 relative border-t border-white/5" id="how-it-works">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16 animate-up">
                <h2 class="text-2xl md:text-4xl font-bold mb-4 text-gradient">Secure in 4 Steps</h2>
                <p class="text-zinc-400 text-sm md:text-base font-light">The end-to-end lifecycle of a file secured inside the FMSS vault.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 relative">
                <!-- Connecting Line Desktop -->
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-y-1/2 z-0"></div>
                
                <!-- Step 1 -->
                <div class="relative z-10 glass-card rounded-2xl p-6 text-center animate-up">
                    <div class="w-10 h-10 md:w-12 md:h-12 mx-auto rounded-full bg-black border border-zinc-800 flex items-center justify-center mb-4 text-green-400 font-mono font-bold text-sm md:text-base">1</div>
                    <h4 class="text-white font-medium mb-2 text-sm md:text-base">Local Encryption</h4>
                    <p class="text-zinc-500 text-xs md:text-sm">Files are locked using AES-256-GCM in your browser before upload.</p>
                </div>
                <!-- Step 2 -->
                <div class="relative z-10 glass-card rounded-2xl p-6 text-center animate-up">
                    <div class="w-10 h-10 md:w-12 md:h-12 mx-auto rounded-full bg-black border border-zinc-800 flex items-center justify-center mb-4 text-white font-mono font-bold text-sm md:text-base">2</div>
                    <h4 class="text-white font-medium mb-2 text-sm md:text-base">Fingerprinting</h4>
                    <p class="text-zinc-500 text-xs md:text-sm">A unique SHA-256 hash is generated to mathematically prove file integrity.</p>
                </div>
                <!-- Step 3 -->
                <div class="relative z-10 glass-card rounded-2xl p-6 text-center animate-up">
                    <div class="w-10 h-10 md:w-12 md:h-12 mx-auto rounded-full bg-black border border-zinc-800 flex items-center justify-center mb-4 text-white font-mono font-bold text-sm md:text-base">3</div>
                    <h4 class="text-white font-medium mb-2 text-sm md:text-base">Ledger Anchor</h4>
                    <p class="text-zinc-500 text-xs md:text-sm">The upload event and hash are permanently bound into the hash-chain log.</p>
                </div>
                <!-- Step 4 -->
                <div class="relative z-10 glass-card rounded-2xl p-6 text-center animate-up">
                    <div class="w-10 h-10 md:w-12 md:h-12 mx-auto rounded-full bg-black border border-zinc-800 flex items-center justify-center mb-4 text-green-400 font-mono font-bold text-sm md:text-base">4</div>
                    <h4 class="text-white font-medium mb-2 text-sm md:text-base">Signed Share</h4>
                    <p class="text-zinc-500 text-xs md:text-sm">You grant access by re-wrapping the key and signing it with your digital identity.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Use Cases Section -->
    <section class="py-20 md:py-32 bg-zinc-950 border-y border-white/5 relative overflow-hidden" id="use-cases">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div class="animate-up">
                    <h2 class="text-2xl md:text-4xl font-bold mb-6 text-gradient">Built for Absolute Privacy</h2>
                    <p class="text-zinc-400 text-sm md:text-base leading-relaxed mb-8">From personal documents to enterprise source code, FMSS scales its zero-knowledge architecture to fit any security requirement.</p>
                    
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <i class="ph ph-user text-2xl text-green-400 shrink-0"></i>
                            <div>
                                <h4 class="text-white font-medium text-sm md:text-base">Personal Vault</h4>
                                <p class="text-zinc-500 text-xs md:text-sm mt-1">Store passports, tax records, and recovery keys where literally nobody else can read them.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <i class="ph ph-users text-2xl text-white shrink-0"></i>
                            <div>
                                <h4 class="text-white font-medium text-sm md:text-base">Secure Team Handoff</h4>
                                <p class="text-zinc-500 text-xs md:text-sm mt-1">Transfer sensitive client data, source code, and credentials using verifiable signed shares.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <i class="ph ph-buildings text-2xl text-zinc-500 shrink-0"></i>
                            <div>
                                <h4 class="text-white font-medium text-sm md:text-base">Enterprise Audit Compliance</h4>
                                <p class="text-zinc-500 text-xs md:text-sm mt-1">Satisfy stringent compliance requirements with the unalterable, cryptographically linked action ledger.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Abstract Graphic -->
                <div class="relative w-64 h-64 mx-auto md:w-full md:h-auto aspect-square glass-card rounded-full flex items-center justify-center p-8 animate-up border border-white/5">
                    <div class="absolute inset-0 bg-green-500/5 blur-[80px] rounded-full pointer-events-none"></div>
                    <i class="ph ph-lock-key text-[80px] md:text-[120px] text-zinc-800/50"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Pricing Section -->
    <section class="py-20 md:py-32 relative" id="pricing">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16 animate-up">
                <h2 class="text-2xl md:text-4xl font-bold mb-4 text-gradient">Transparent Security</h2>
                <p class="text-zinc-400 text-sm md:text-base font-light">Zero-knowledge architecture, available to everyone. Scale up when you need more storage.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <!-- Free Tier -->
                <div class="glass-card rounded-2xl p-6 md:p-8 border border-white/5 flex flex-col animate-up">
                    <h3 class="text-lg md:text-xl font-medium text-white mb-2">Free</h3>
                    <div class="text-2xl md:text-4xl font-bold text-white mb-6">$0<span class="text-xs md:text-sm font-normal text-zinc-500">/mo</span></div>
                    <ul class="space-y-4 mb-8 flex-1">
                        <li class="flex items-center gap-3 text-xs md:text-sm text-zinc-400"><i class="ph ph-check text-green-400"></i> 10 GB Encrypted Storage</li>
                        <li class="flex items-center gap-3 text-xs md:text-sm text-zinc-400"><i class="ph ph-check text-green-400"></i> Up to 5 recipients/share</li>
                        <li class="flex items-center gap-3 text-xs md:text-sm text-zinc-400"><i class="ph ph-check text-green-400"></i> 2 GB Max File Size</li>
                    </ul>
                    <button class="w-full py-2.5 md:py-3 px-4 rounded-xl glass-card text-white text-xs md:text-sm font-medium hover:bg-white/10 transition-colors">Start Free</button>
                </div>
                
                <!-- Pro Tier -->
                <div class="glass-card rounded-2xl p-6 md:p-8 border border-green-500/30 bg-green-500/5 flex flex-col relative transform md:-translate-y-4 shadow-[0_0_30px_rgba(34,197,94,0.1)] animate-up">
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-green-500 text-black text-[10px] md:text-xs font-bold px-3 py-1 rounded-full">RECOMMENDED</div>
                    <h3 class="text-lg md:text-xl font-medium text-green-400 mb-2">Pro</h3>
                    <div class="text-2xl md:text-4xl font-bold text-white mb-6">$9<span class="text-xs md:text-sm font-normal text-zinc-500">/mo</span></div>
                    <ul class="space-y-4 mb-8 flex-1">
                        <li class="flex items-center gap-3 text-xs md:text-sm text-zinc-300"><i class="ph ph-check text-green-400"></i> 200 GB Encrypted Storage</li>
                        <li class="flex items-center gap-3 text-xs md:text-sm text-zinc-300"><i class="ph ph-check text-green-400"></i> 10 GB Max File Size</li>
                        <li class="flex items-center gap-3 text-xs md:text-sm text-zinc-300"><i class="ph ph-check text-green-400"></i> Custom Share Links</li>
                    </ul>
                    <button class="w-full py-2.5 md:py-3 px-4 rounded-xl bg-green-500 text-black text-xs md:text-sm font-bold hover:bg-green-400 transition-colors">Upgrade to Pro</button>
                </div>
                
                <!-- Business Tier -->
                <div class="glass-card rounded-2xl p-6 md:p-8 border border-white/5 flex flex-col animate-up">
                    <h3 class="text-lg md:text-xl font-medium text-white mb-2">Business</h3>
                    <div class="text-2xl md:text-4xl font-bold text-white mb-6">$29<span class="text-xs md:text-sm font-normal text-zinc-500">/user</span></div>
                    <ul class="space-y-4 mb-8 flex-1">
                        <li class="flex items-center gap-3 text-xs md:text-sm text-zinc-400"><i class="ph ph-check text-green-400"></i> 1 TB+ Encrypted Storage</li>
                        <li class="flex items-center gap-3 text-xs md:text-sm text-zinc-400"><i class="ph ph-check text-green-400"></i> Team Shared Vaults</li>
                        <li class="flex items-center gap-3 text-xs md:text-sm text-zinc-400"><i class="ph ph-check text-green-400"></i> Audit Log Export</li>
                    </ul>
                    <button class="w-full py-2.5 md:py-3 px-4 rounded-xl glass-card text-white text-xs md:text-sm font-medium hover:bg-white/10 transition-colors">Contact Sales</button>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. FAQ Section -->
    <section class="py-20 md:py-32 bg-zinc-950 border-t border-white/5 relative" id="faq">
        <div class="max-w-3xl mx-auto px-6">
            <div class="text-center mb-12 animate-up">
                <h2 class="text-2xl md:text-4xl font-bold mb-4 text-gradient">Frequently Asked Questions</h2>
            </div>
            
            <div class="space-y-4 animate-up">
                <details class="glass-card border border-white/5 rounded-xl p-4 md:p-6 group cursor-pointer">
                    <summary class="text-sm md:text-base font-medium text-white flex justify-between items-center outline-none">
                        What exactly is zero-knowledge encryption?
                        <i class="ph ph-caret-down text-zinc-400 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="text-zinc-400 mt-4 text-xs md:text-sm leading-relaxed">
                        It means we (the service providers) have absolutely no ability to see, read, or scan your files. Everything is encrypted directly in your web browser before it is ever sent to our servers.
                    </p>
                </details>
                
                <details class="glass-card border border-white/5 rounded-xl p-4 md:p-6 group cursor-pointer">
                    <summary class="text-sm md:text-base font-medium text-white flex justify-between items-center outline-none">
                        What happens if I lose my password?
                        <i class="ph ph-caret-down text-zinc-400 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="text-zinc-400 mt-4 text-xs md:text-sm leading-relaxed">
                        Because of our zero-knowledge architecture, we cannot reset your password. When you create an account, you are provided with a one-time downloadable Recovery Kit.
                    </p>
                </details>
                
                <details class="glass-card border border-white/5 rounded-xl p-4 md:p-6 group cursor-pointer">
                    <summary class="text-sm md:text-base font-medium text-white flex justify-between items-center outline-none">
                        Do I need cryptocurrency to use the Ledger?
                        <i class="ph ph-caret-down text-zinc-400 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <p class="text-zinc-400 mt-4 text-xs md:text-sm leading-relaxed">
                        No. While we use blockchain techniques (cryptographic hash chains) to secure our audit logs, FMSS does not use or require any cryptocurrency or tokens.
                    </p>
                </details>
            </div>
        </div>
    </section>

    <!-- 5. Call to Action (CTA) -->
    <section class="py-24 md:py-32 relative overflow-hidden" id="cta">
        <div class="absolute inset-0 bg-green-500/10 blur-[120px] z-0 pointer-events-none"></div>
        <div class="max-w-4xl mx-auto px-6 relative z-10 text-center">
            <h2 class="text-3xl md:text-5xl font-bold mb-6 text-white">Ready to lock down your assets?</h2>
            <p class="text-zinc-400 text-sm md:text-base mb-8 md:mb-10 max-w-2xl mx-auto font-light">Join the most secure, provably authentic file vault. Start with 10 GB of zero-knowledge storage, completely free.</p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 md:gap-4">
                <button class="w-full sm:w-auto px-6 py-3 rounded-xl bg-white text-black font-bold hover:bg-zinc-200 transition-all flex items-center justify-center gap-2 text-sm md:text-base">
                    Create Free Account
                </button>
                <button class="w-full sm:w-auto px-6 py-3 rounded-xl glass-card text-white font-medium hover:bg-white/10 transition-colors flex items-center justify-center gap-2 text-sm md:text-base border border-white/10">
                    Read the Documentation
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 bg-black pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <i class="ph ph-shield-check text-2xl text-green-500"></i>
                <span class="font-mono font-bold text-lg tracking-widest text-zinc-400">FMSS</span>
            </div>
            <div class="text-sm text-zinc-600 font-mono">
                Encrypted at rest. Secured in transit. Verified by mathematics.
            </div>
            <div class="flex gap-4 text-zinc-500">
                <a href="#" class="hover:text-green-400 transition-colors"><i class="ph ph-github-logo text-xl"></i></a>
                <a href="#" class="hover:text-green-400 transition-colors"><i class="ph ph-twitter-logo text-xl"></i></a>
            </div>
        </div>
    </footer>

    <!-- Motion.dev animations script (Vanilla JS Implementation) -->
    <script type="module">
        import { animate, inView, stagger } from "https://cdn.jsdelivr.net/npm/motion@11.11.13/+esm";

        // 1. Initial Hero Stagger Animation
        animate(
            ".hero-content > *",
            { opacity: [0, 1], y: [30, 0] },
            { delay: stagger(0.15), duration: 0.8, easing: [0.22, 1, 0.36, 1] }
        );

        animate(
            ".hero-spline",
            { opacity: [0, 1], scale: [0.95, 1] },
            { delay: 0.4, duration: 1.2, easing: [0.22, 1, 0.36, 1] }
        );

        // 2. Trust Banner Fade in
        inView(".stagger-banner > div", (info) => {
            animate(
                info.target,
                { opacity: [0, 1], y: [10, 0] },
                { delay: stagger(0.1), duration: 0.5 }
            );
        });

        // 3. Features Grid Scroll Animation
        inView(".section-header", (info) => {
            animate(info.target, { opacity: [0, 1], y: [40, 0] }, { duration: 0.8 });
        });

        inView(".feature-card", (info) => {
            animate(
                ".feature-card",
                { opacity: [0, 1], y: [50, 0] },
                { delay: stagger(0.15), duration: 0.8, easing: [0.22, 1, 0.36, 1] }
            );
        }, { margin: "-100px" });

        // 4. Ledger Section Animation
        inView(".ledger-visual", (info) => {
            animate(info.target, { opacity: [0, 1], x: [-50, 0] }, { duration: 0.8 });
        });
        
        inView(".ledger-text", (info) => {
            animate(info.target, { opacity: [0, 1], x: [50, 0] }, { duration: 0.8 });
        });

        // New Sections generic scroll animation
        inView(".animate-up", (info) => {
            animate(info.target, { opacity: [0, 1], y: [40, 0] }, { duration: 0.8 });
        });

        // 5. Vanilla JS Spotlight Effect (replacing React 'Spotlight' component)
        const heroContainer = document.getElementById('hero');
        const spotlightOverlay = document.getElementById('spotlight-overlay');

        heroContainer.addEventListener('mousemove', (e) => {
            const rect = heroContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            spotlightOverlay.style.setProperty('--x', `${x}px`);
            spotlightOverlay.style.setProperty('--y', `${y}px`);
        });

        // 6. Navbar Glass Effect on Scroll
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('bg-black/80', 'backdrop-blur-md', 'border-white/10');
            } else {
                navbar.classList.remove('bg-black/80', 'backdrop-blur-md', 'border-white/10');
            }
        });

        // 7. Mobile Menu Toggle Logic
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const closeMenuBtn = document.getElementById('close-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuBackdrop = document.getElementById('mobile-menu-backdrop');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const mobileLinks = document.querySelectorAll('.mobile-link');

        function toggleMenu() {
            const isHidden = mobileMenu.classList.contains('hidden');
            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                void mobileMenu.offsetWidth; // trigger reflow
                mobileMenuBackdrop.classList.remove('opacity-0');
                mobileSidebar.classList.remove('translate-x-full');
                document.body.style.overflow = 'hidden'; // prevent scrolling behind menu
            } else {
                mobileMenuBackdrop.classList.add('opacity-0');
                mobileSidebar.classList.add('translate-x-full');
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            }
        }

        mobileMenuBtn.addEventListener('click', toggleMenu);
        closeMenuBtn.addEventListener('click', toggleMenu);
        mobileMenuBackdrop.addEventListener('click', toggleMenu);
        mobileLinks.forEach(link => link.addEventListener('click', toggleMenu));
    </script>
</body>
</html>