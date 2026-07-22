<section class="section">
    <a href="?page=account" class="btn btn-outline-secondary mb-4">
        Retour à mon espace
    </a>

    <h1>Commande n°<?= (int) $order['id'] ?></h1>

    <?php if ($orderCreated): ?>
        <div class="alert alert-success" role="status">
            Votre commande a été enregistrée et un e-mail de confirmation a été envoyé.
        </div>
    <?php endif; ?>

    <?php if ($orderUpdated): ?>
        <div class="alert alert-success js-auto-hide" role="status">
            Votre commande a été mise à jour.
        </div>
    <?php endif; ?>

    <?php if ($orderCanceled): ?>
        <div class="alert alert-success" role="status">
            Votre commande a été annulée.
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card p-4 h-100">
                <h2 class="h4">Prestation</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Menu</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($order['menu_title']) ?></dd>
                    <dt class="col-sm-5">Date et heure</dt>
                    <dd class="col-sm-7">
                        <?= htmlspecialchars($order['event_date']) ?>
                        à <?= htmlspecialchars(substr((string) $order['event_time'], 0, 5)) ?>
                    </dd>
                    <dt class="col-sm-5">Adresse</dt>
                    <dd class="col-sm-7">
                        <?= htmlspecialchars($order['delivery_address']) ?>,
                        <?= htmlspecialchars($order['delivery_city']) ?>
                    </dd>
                    <dt class="col-sm-5">Personnes</dt>
                    <dd class="col-sm-7"><?= (int) $order['people_count'] ?></dd>
                    <dt class="col-sm-5">Statut</dt>
                    <dd class="col-sm-7">
                        <span class="badge text-bg-primary">
                            <?= htmlspecialchars($statuses[$order['status']] ?? $order['status']) ?>
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card p-4 h-100">
                <h2 class="h4">Prix</h2>
                <dl class="row mb-0">
                    <dt class="col-7">Menu</dt>
                    <dd class="col-5 text-end">
                        <?= number_format((float) $order['menu_price'], 2, ',', ' ') ?> €
                    </dd>
                    <dt class="col-7">Livraison</dt>
                    <dd class="col-5 text-end">
                        <?= number_format((float) $order['delivery_price'], 2, ',', ' ') ?> €
                    </dd>
                    <dt class="col-7">Réduction</dt>
                    <dd class="col-5 text-end">
                        - <?= number_format((float) $order['discount_amount'], 2, ',', ' ') ?> €
                    </dd>
                    <dt class="col-7 border-top pt-2">Total</dt>
                    <dd class="col-5 text-end border-top pt-2 fw-bold">
                        <?= number_format((float) $order['total_price'], 2, ',', ' ') ?> €
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <?php if ($order['status'] === 'nouvelle'): ?>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="?page=order-edit&amp;id=<?= (int) $order['id'] ?>"
                class="btn btn-primary">
                Modifier la commande
            </a>
            <form method="post" action="?page=order-cancel"
                class="js-confirm-form"
                data-confirm="Annuler définitivement cette commande ?">
                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger">
                    Annuler la commande
                </button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($order['status'] === 'annulee' && !empty($order['cancellation_reason'])): ?>
        <div class="alert alert-secondary mt-4">
            <strong>Motif d’annulation :</strong>
            <?= htmlspecialchars($order['cancellation_reason']) ?>
        </div>
    <?php endif; ?>

    <section class="mt-5" aria-labelledby="history-title">
        <h2 id="history-title">Suivi de la commande</h2>
        <ol class="order-timeline">
            <?php foreach ($history as $event): ?>
                <li>
                    <div class="fw-bold">
                        <?= htmlspecialchars($statuses[$event['status']] ?? $event['status']) ?>
                    </div>
                    <div>
                        <?= htmlspecialchars($event['created_at']) ?>
                        <?php if (!empty($event['first_name'])): ?>
                            - par <?= htmlspecialchars(
                                $event['first_name'] . ' ' . $event['last_name']
                            ) ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($event['comment'])): ?>
                        <p class="mb-0"><?= htmlspecialchars($event['comment']) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
</section>
