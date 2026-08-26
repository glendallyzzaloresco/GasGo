window.loadTheme = async function loadTheme() {
    const appRoot = document.documentElement;

    try {
        const basePath = window.location.pathname.includes('/public/')
            ? window.location.pathname.split('/public/')[0] + '/public'
            : '';
        const response = await fetch(basePath + '/api/theme', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Unable to load theme');
        }

        const payload = await response.json();
        const theme = payload?.data ?? payload ?? {};

        appRoot.style.setProperty('--color-primary', theme.primaryColor || '#1a6db0');
        appRoot.style.setProperty('--color-accent', theme.accentColor || '#f7941d');
        appRoot.style.setProperty('--color-background', theme.backgroundColor || '#f4f7fb');
        appRoot.style.setProperty('--sidebar-bg', theme.sidebarBackground || '#111b35');

        appRoot.style.setProperty('--gasgo-blue', theme.primaryColor || '#1a6db0');
        appRoot.style.setProperty('--gasgo-orange', theme.accentColor || '#f7941d');
        appRoot.style.setProperty('--admin-bg', theme.backgroundColor || '#f4f7fb');

        const logoElements = document.querySelectorAll('[data-theme-logo]');
        if (logoElements.length && (theme.logoUrl || theme.logo_url)) {
            let logoUrl = theme.logoUrl || theme.logo_url;
            if (typeof logoUrl === 'string' && logoUrl.trim() !== '') {
                try {
                    if (logoUrl.startsWith('http://') || logoUrl.startsWith('https://')) {
                        const parsed = new URL(logoUrl);
                        if (parsed.hostname === 'localhost' || parsed.hostname === '127.0.0.1') {
                            let cleanPath = parsed.pathname;
                            logoUrl = (basePath || '') + cleanPath;
                        }
                    } else if (logoUrl.startsWith('/')) {
                        logoUrl = (basePath || '') + logoUrl;
                    }
                } catch (e) {
                    // Keep existing logoUrl
                }

                logoElements.forEach((element) => {
                    element.onerror = function () {
                        this.onerror = null;
                        this.src = (basePath || '') + '/images/logo-gasgo.png';
                    };
                    element.src = logoUrl;
                });
            }
        }

        document.dispatchEvent(new CustomEvent('gasgo:theme-loaded', { detail: theme }));
    } catch (error) {
        console.warn('Theme loader failed:', error);
    }
};

window.addEventListener('DOMContentLoaded', function () {
    window.loadTheme();
});
