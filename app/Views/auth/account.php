<section class="section">
    <h1>Mon espace</h1>

    <?php if ($reviewCreated): ?>
        <div class="alert alert-success js-auto-hide">Votre avis a bien été envoyé.</div>
    <?php endif; ?>

    <?php if ($profileUpdated): ?>
        <div class="alert alert-success js-auto-hide">
            Vos informations ont bien été mises à jour.
        </div>
    <?php endif; ?>

    <?php if (!$reviewsAvailable): ?>
        <div class="alert alert-warning" role="status">
            Le service d’avis est temporairement indisponible. Le suivi de vos
            commandes reste accessible.
        </div>
    <?php endif; ?>

    <div class="card p-4 mb-4">
        <p>Bienvenue <?= htmlspecialchars($user['first_name']) ?>.</p>
        <p>Rôle : <?= htmlspecialchars($user['role']) ?></p>

        <form method="post" action="?page=logout">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger">
                Se déconnecter
            </button>
        </form>
    </div>

    <h2 class="h3">Mes informations</h2>

    <?php if (!empty($profileErrors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($profileErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-4 mb-4">
        <input type="hidden" name="form_action" value="update_profile">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="first_name" class="form-label">Prénom</label>
                <input type="text" id="first_name" name="first_name" class="form-control"
                    minlength="2" maxlength="100" autocomplete="given-name"
                    value="<?= htmlspecialchars($profile['first_name']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="last_name" class="form-label">Nom</label>
                <input type="text" id="last_name" name="last_name" class="form-control"
                    minlength="2" maxlength="100" autocomplete="family-name"
                    value="<?= htmlspecialchars($profile['last_name']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input type="email" id="email" name="email" class="form-control"
                    maxlength="180" autocomplete="email"
                    value="<?= htmlspecialchars($profile['email']) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                    maxlength="30" autocomplete="tel"
                    value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label for="address" class="form-label">Adresse</label>
                <input type="text" id="address" name="address" class="form-control"
                    maxlength="255" autocomplete="street-address"
                    value="<?= htmlspecialchars($profile['address'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label for="postal_code" class="form-label">Code postal</label>
                <input type="text" id="postal_code" name="postal_code" class="form-control"
                    maxlength="20" autocomplete="postal-code"
                    value="<?= htmlspecialchars($profile['postal_code'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label for="city" class="form-label">Ville</label>
                <input type="text" id="city" name="city" class="form-control"
                    maxlength="100" autocomplete="address-level2"
                    value="<?= htmlspecialchars($profile['city'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn-app">Enregistrer mes informations</button>
    </form>

    <h2>Mes commandes</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Vous n'avez pas encore passé de commande.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Menu</th>
                        <th>Date événement</th>
                        <th>Personnes</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['menu_title']) ?></td>
                            <td><?= htmlspecialchars($order['event_date']) ?> <?= htmlspecialchars($order['event_time']) ?></td>
                            <td><?= (int) $order['people_count'] ?></td>
                            <td><?= number_format((float) $order['total_price'], 2, ',', ' ') ?> €</td>
                            <td><?= htmlspecialchars($statuses[$order['status']] ?? $order['status']) ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a
                                        href="?page=order-show&amp;id=<?= (int) $order['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Détail
                                    </a>

                                    <?php if ($order['can_modify']): ?>
                                        <a
                                            href="?page=order-edit&amp;id=<?= (int) $order['id'] ?>"
                                            class="btn btn-sm btn-primary"
                                        >
                                            Modifier
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($order['existing_review'] !== null): ?>
                                        <span class="badge text-bg-success align-self-center">
                                            Avis envoyé
                                        </span>
                                    <?php elseif ($order['can_review']): ?>
                                        <a
                                            href="?page=review-create&amp;order_id=<?= (int) $order['id'] ?>"
                                            class="btn btn-sm btn-success"
                                        >
                                            Donner mon avis
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
