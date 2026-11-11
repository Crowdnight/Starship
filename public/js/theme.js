(() => {
    const storageKey = 'daily-notes-theme';
    const savedTheme = localStorage.getItem(storageKey);

    const defaultTheme = document.documentElement.dataset.adminPanel === 'true' ? 'dark' : 'light';
    document.documentElement.dataset.theme = savedTheme === 'dark' || savedTheme === 'light'
        ? savedTheme
        : defaultTheme;

    const updateToggle = (button) => {
        const isDark = document.documentElement.dataset.theme === 'dark';
        button.setAttribute('aria-pressed', String(isDark));
        button.setAttribute('aria-label', isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
        button.title = isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap';
        button.querySelector('[data-theme-icon]').textContent = isDark ? '☀' : '☾';

        const label = button.querySelector('[data-theme-label]');
        if (label) {
            label.textContent = isDark ? 'Mode terang' : 'Mode gelap';
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            updateToggle(button);
            button.addEventListener('click', () => {
                const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
                document.documentElement.dataset.theme = theme;
                localStorage.setItem(storageKey, theme);
                document.querySelectorAll('[data-theme-toggle]').forEach(updateToggle);
            });
        });
    });
})();
