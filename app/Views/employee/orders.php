<section class="section">
    <h1>Gestion des commandes</h1>

    <?php if ($orderUpdated): ?>
        <div class="alert alert-success js-auto-hide" role="status">
            La commande a été mise à jour et le client a été notifié.
        </div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="get" class="card p-4 mb-4">
        <input type="hidden" name="page" value="employee-orders">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="status_filter" class="form-label">Statut</label>
                <select id="status_filter" name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>"
                            <?= $selectedStatus === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="customer_filter" class="form-label">Client</label>
                <input type="search" id="customer_filter" name="customer"
                    class="form-control" value="<?= htmlspecialchars($customerSearch) ?>"
                    placeholder="Nom ou adresse e-mail">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="?page=employee-orders" class="btn btn-outline-secondary">
                    Effacer
                </a>
            </div>
        </div>
    </form>

    <?php if ($orders === []): ?>
        <div class="alert alert-info">Aucune commande ne correspond aux filtres.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <caption class="visually-hidden">Commandes à traiter</caption>
                <thead>
                    <tr>
                        <th scope="col">Client</th>
                        <th scope="col">Menu</th>
                        <th scope="col">Prestation</th>
                        <th scope="col">Total</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Prochaine étape</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $nextStatuses = $transitions[$order['status']] ?? [];
                        $formId = 'order_' . (int) $order['id'];
                        ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?><br>
                                <a href="mailto:<?= htmlspecialchars($order['email']) ?>">
                                    <?= htmlspecialchars($order['email']) ?>
                                </a><br>
                                <?php if (!empty($order['phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($order['phone']) ?>">
                                        <?= htmlspecialchars($order['phone']) ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($order['menu_title']) ?></td>
                            <td>
                                <?= htmlspecialchars($order['event_date']) ?>
                                <?= htmlspecialchars(substr((string) $order['event_time'], 0, 5)) ?><br>
                                <?= (int) $order['people_count'] ?> personnes
                            </td>
                            <td><?= number_format((float) $order['total_price'], 2, ',', ' ') ?> €</td>
                            <td>
                                <?= htmlspecialchars($statuses[$order['status']] ?? $order['status']) ?>
                            </td>
                            <td>
                                <?php if ($nextStatuses === []): ?>
                                    <span class="text-muted">Traitement terminé</span>
                                <?php else: ?>
                                    <form method="post" class="order-status-form">
                                        <input type="hidden" name="action"
                                            value="update_order_status">
                                        <input type="hidden" name="order_id"
                                            value="<?= (int) $order['id'] ?>">
                                        <?= csrf_field() ?>

                                        <label for="<?= $formId ?>_status"
                                            class="visually-hidden">
                                            Nouveau statut
                                        </label>
                                        <select id="<?= $formId ?>_status" name="status"
                                            class="form-select form-select-sm js-order-status"
                                            required>
                                            <option value="">Choisir</option>
                                            <?php foreach ($nextStatuses as $status): ?>
                                                <option value="<?= htmlspecialchars($status) ?>">
                                                    <?= htmlspecialchars($statuses[$status]) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <div class="js-cancellation-fields mt-2" hidden>
                                            <label for="<?= $formId ?>_contact"
                                                class="form-label small">
                                                Client contacté par
                                            </label>
                                            <select id="<?= $formId ?>_contact"
                                                name="contact_method"
                                                class="form-select form-select-sm">
                                                <option value="">Choisir</option>
                                                <option value="telephone">Téléphone</option>
                                                <option value="email">E-mail</option>
                                            </select>

                                            <label for="<?= $formId ?>_reason"
                                                class="form-label small mt-2">
                                                Motif d’annulation
                                            </label>
                                            <textarea id="<?= $formId ?>_reason"
                                                name="cancellation_reason"
                                                class="form-control form-control-sm"
                                                minlength="10" maxlength="500"
                                                rows="3"></textarea>
                                        </div>

                                        <button type="submit"
                                            class="btn btn-sm btn-primary mt-2">
                                            Mettre à jour
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
