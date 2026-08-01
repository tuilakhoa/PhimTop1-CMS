    <footer class="footer">
        <div class="container">
            <div style="display: flex; justify-content: center; gap: 24px; margin-bottom: 24px;">
                <a href="#" style="color: var(--color-text-muted);"><i data-lucide="facebook"></i></a>
                <a href="#" style="color: var(--color-text-muted);"><i data-lucide="twitter"></i></a>
                <a href="#" style="color: var(--color-text-muted);"><i data-lucide="youtube"></i></a>
            </div>
            <p class="footer-text">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. Nền tảng xem phim trực tuyến hàng đầu.</p>
            <p style="font-size: 12px; color: #4b5563; margin-top: 8px;">Giao diện Premium xây dựng bằng Vanilla CSS.</p>
        </div>
    </footer>

    <!-- Initialize Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
