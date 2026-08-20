import { animate } from "https://cdn.jsdelivr.net/npm/motion@11.11.13/+esm";

export class UI {
    static initToasts() {
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none';
            document.body.appendChild(container);
        }
    }

    static showToast(message, type = 'info') {
        this.initToasts();
        const container = document.getElementById('toast-container');
        
        const icons = {
            success: '<i class="ph ph-check-circle text-green-500 text-xl"></i>',
            error: '<i class="ph ph-warning-circle text-red-500 text-xl"></i>',
            info: '<i class="ph ph-info text-blue-500 text-xl"></i>',
            warning: '<i class="ph ph-warning text-yellow-500 text-xl"></i>'
        };

        const borders = {
            success: 'border-green-500/30 bg-green-500/10 text-green-50',
            error: 'border-red-500/30 bg-red-500/10 text-red-50',
            info: 'border-blue-500/30 bg-blue-500/10 text-blue-50',
            warning: 'border-yellow-500/30 bg-yellow-500/10 text-yellow-50'
        };

        const toast = document.createElement('div');
        toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl border ${borders[type]} backdrop-blur-xl shadow-2xl pointer-events-auto overflow-hidden relative group min-w-[250px]`;
        
        const progress = document.createElement('div');
        progress.className = `absolute bottom-0 left-0 h-[2px] bg-current opacity-30 w-full origin-left`;
        
        toast.innerHTML = `
            ${icons[type]}
            <p class="text-sm font-medium pr-6">${message}</p>
            <button class="absolute top-1/2 -translate-y-1/2 right-3 text-white/50 hover:text-white transition-colors toast-close">
                <i class="ph ph-x"></i>
            </button>
        `;
        toast.appendChild(progress);
        container.appendChild(toast);

        // Animate in
        animate(toast, { opacity: [0, 1], x: [50, 0], scale: [0.9, 1] }, { duration: 0.5, easing: [0.22, 1, 0.36, 1] });
        
        // Progress bar animation
        const duration = 4000;
        const progressAnim = animate(progress, { scaleX: [1, 0] }, { duration: duration / 1000, easing: "linear" });

        const removeToast = async () => {
            progressAnim.stop();
            await animate(toast, { opacity: 0, x: 50, scale: 0.9 }, { duration: 0.3 }).finished;
            toast.remove();
        };

        let timeout = setTimeout(removeToast, duration);

        toast.querySelector('.toast-close').addEventListener('click', () => {
            clearTimeout(timeout);
            removeToast();
        });
    }

    static showAlert(options = {}) {
        return new Promise((resolve) => {
            const {
                title = 'Alert',
                message = '',
                type = 'info', // info, warning, danger, success
                confirmText = 'OK',
                cancelText = null,
                isDismissible = true
            } = options;

            const backdrop = document.createElement('div');
            backdrop.className = 'fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm';
            
            const icons = {
                success: '<div class="w-14 h-14 rounded-full bg-green-500/10 border border-green-500/30 flex items-center justify-center mb-5"><i class="ph ph-check text-3xl text-green-500"></i></div>',
                danger: '<div class="w-14 h-14 rounded-full bg-red-500/10 border border-red-500/30 flex items-center justify-center mb-5"><i class="ph ph-warning-circle text-3xl text-red-500"></i></div>',
                warning: '<div class="w-14 h-14 rounded-full bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center mb-5"><i class="ph ph-warning text-3xl text-yellow-500"></i></div>',
                info: '<div class="w-14 h-14 rounded-full bg-blue-500/10 border border-blue-500/30 flex items-center justify-center mb-5"><i class="ph ph-info text-3xl text-blue-500"></i></div>'
            };

            const btnColors = {
                success: 'bg-green-500 hover:bg-green-400 text-black shadow-[0_0_15px_rgba(34,197,94,0.3)]',
                danger: 'bg-red-500 hover:bg-red-400 text-white shadow-[0_0_15px_rgba(239,68,68,0.3)]',
                warning: 'bg-yellow-500 hover:bg-yellow-400 text-black shadow-[0_0_15px_rgba(234,179,8,0.3)]',
                info: 'bg-blue-500 hover:bg-blue-400 text-white shadow-[0_0_15px_rgba(59,130,246,0.3)]'
            };

            backdrop.innerHTML = `
                <div class="modal-box w-full max-w-sm bg-zinc-950/90 border border-white/10 rounded-3xl p-8 shadow-2xl relative backdrop-blur-xl">
                    ${isDismissible ? `<button class="absolute top-5 right-5 text-zinc-500 hover:text-white transition-colors modal-close"><i class="ph ph-x text-xl"></i></button>` : ''}
                    
                    <div class="flex flex-col items-center text-center">
                        ${icons[type] || icons.info}
                        <h3 class="text-2xl font-bold text-white mb-2 tracking-tight">${title}</h3>
                        <p class="text-zinc-400 text-sm mb-8 leading-relaxed">${message}</p>
                        
                        <div class="flex gap-3 w-full">
                            ${cancelText ? `<button class="flex-1 py-3 rounded-xl border border-white/10 text-white font-medium hover:bg-white/5 transition-colors modal-cancel">${cancelText}</button>` : ''}
                            <button class="flex-1 py-3 rounded-xl font-bold transition-all modal-confirm ${btnColors[type] || btnColors.info}">${confirmText}</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(backdrop);
            const modalBox = backdrop.querySelector('.modal-box');

            // Animate In
            animate(backdrop, { opacity: [0, 1] }, { duration: 0.3 });
            animate(modalBox, { opacity: [0, 1], y: [20, 0], scale: [0.95, 1] }, { duration: 0.5, easing: [0.22, 1, 0.36, 1] });

            const closeModal = async (result) => {
                animate(modalBox, { opacity: 0, y: 10, scale: 0.95 }, { duration: 0.2 });
                await animate(backdrop, { opacity: 0 }, { duration: 0.3 }).finished;
                backdrop.remove();
                resolve(result);
            };

            backdrop.querySelector('.modal-confirm').addEventListener('click', () => closeModal(true));
            if (cancelText) {
                backdrop.querySelector('.modal-cancel').addEventListener('click', () => closeModal(false));
            }
            if (isDismissible) {
                backdrop.querySelector('.modal-close')?.addEventListener('click', () => closeModal(false));
                backdrop.addEventListener('click', (e) => {
                    if (e.target === backdrop) closeModal(false);
                });
            }
        });
    }
}