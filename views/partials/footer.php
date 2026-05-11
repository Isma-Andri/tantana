    <!-- Footer -->
    <footer class="mt-16 border-t border-ink-100 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="font-display font-bold text-ink text-lg">Tantana</span>
            <p class="text-xs text-ink-500">© <?= date('Y') ?> — Ismaël Andrimalala ©</p>
        </div>
    </footer>

    <script>
    // Auto-dismiss flash après 5 secondes
    setTimeout(() => {
        const flash = document.querySelector('[role="alert"]');
        if (flash) flash.style.transition = 'opacity .4s', flash.style.opacity = '0',
            setTimeout(() => flash.remove(), 400);
    }, 5000);
    </script>
</body>
</html>
