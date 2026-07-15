<section class="section">
    <h1>Gestion des commandes</h1>

    <?php if ($orderUpdated): ?>
        <div class="alert alert-success js-auto-hide">Commande mise à jour.</div>
    <?php endif; ?>

    <form method="get" class="card p-4 mb-4">
        <input type="hidden" name="page" value="employee-orders">

        <label class="form-label">Filtrer par statut</label>
        <div class="row g-3">
            <div class="col-md-6">
                <select name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $selectedStatus === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn-app">Filtrer</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Menu</th>
                    <th>Date</th>
                    <th>Personnes</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?><br>
                            <small><?= htmlspecialchars($order['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($order['menu_title']) ?></td>
                        <td><?= htmlspecialchars($order['event_date']) ?> <?= htmlspecialchars($order['event_time']) ?></td>
                        <td><?= (int) $order['people_count'] ?></td>
                        <td><?= number_format((float) $order['total_price'], 2, ',', ' ') ?> €</td>
                        <td><?= htmlspecialchars($statuses[$order['status']] ?? $order['status']) ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                              <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach ($statuses as $value => $label): ?>
                                        <option value="<?= htmlspecialchars($value) ?>" <?= $order['status'] === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit" class="btn btn-sm btn-primary">OK</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
