// assets/js/darkmode.js
function toggleDarkMode() {
    const body = document.body;
    const html = document.documentElement;
    const icon = document.querySelector('#themeToggle i');

    body.classList.toggle('dark-mode');
    html.classList.toggle('dark-mode');

    if (body.classList.contains('dark-mode')) {
        if (icon) icon.className = 'fas fa-sun';
        localStorage.setItem('theme', 'dark');
    } else {
        if (icon) icon.className = 'fas fa-moon';
        localStorage.setItem('theme', 'light');
    }
}

function loadDarkMode() {
    const body = document.body;
    const html = document.documentElement;
    const icon = document.querySelector('#themeToggle i');

    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        html.classList.add('dark-mode');
        if (icon) icon.className = 'fas fa-sun';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadDarkMode();
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleDarkMode);
    }
});