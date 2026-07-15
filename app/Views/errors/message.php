<section class="section">
    <h1><?= htmlspecialchars($heading) ?></h1>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
</section>
