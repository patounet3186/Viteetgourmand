<section class="section">
    <h1>Nous contacter</h1>
    <p>Une question sur un menu ou une commande ? Écrivez-nous.</p>

    <?php if ($messageSent): ?>
        <div class="alert alert-success js-auto-hide">
            Votre message a bien été envoyé.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-4 mt-4">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="full_name" class="form-label">Nom complet</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                class="form-control"
                value="<?= htmlspecialchars($form['full_name']) ?>"
                maxlength="100"
                autocomplete="name"
                required
            >
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                value="<?= htmlspecialchars($form['email']) ?>"
                maxlength="180"
                autocomplete="email"
                required
            >
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Sujet</label>
            <input
                type="text"
                id="subject"
                name="subject"
                class="form-control"
                value="<?= htmlspecialchars($form['subject']) ?>"
                maxlength="150"
                required
            >
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea
                id="message"
                name="message"
                class="form-control"
                rows="6"
                maxlength="3000"
                required
            ><?= htmlspecialchars($form['message']) ?></textarea>
        </div>

        <button type="submit" class="btn-app">Envoyer le message</button>
    </form>
</section>
