<div class="row g-3">
    <div class="col-md-6">
        <label for="<?= $orderFormPrefix ?>_date" class="form-label">
            Date de prestation
        </label>
        <input type="date" id="<?= $orderFormPrefix ?>_date" name="event_date"
            class="form-control" min="<?= date('Y-m-d') ?>"
            value="<?= htmlspecialchars($form['event_date']) ?>" required>
    </div>

    <div class="col-md-6">
        <label for="<?= $orderFormPrefix ?>_time" class="form-label">
            Heure souhaitée
        </label>
        <input type="time" id="<?= $orderFormPrefix ?>_time" name="event_time"
            class="form-control"
            value="<?= htmlspecialchars($form['event_time']) ?>" required>
    </div>

    <div class="col-md-8">
        <label for="<?= $orderFormPrefix ?>_address" class="form-label">
            Adresse de livraison
        </label>
        <input type="text" id="<?= $orderFormPrefix ?>_address"
            name="delivery_address" class="form-control"
            minlength="5" maxlength="255" autocomplete="street-address"
            value="<?= htmlspecialchars($form['delivery_address']) ?>" required>
    </div>

    <div class="col-md-4">
        <label for="<?= $orderFormPrefix ?>_city" class="form-label">Ville</label>
        <input type="text" id="<?= $orderFormPrefix ?>_city"
            name="delivery_city" class="form-control js-order-city"
            minlength="2" maxlength="100" autocomplete="address-level2"
            value="<?= htmlspecialchars($form['delivery_city']) ?>" required>
    </div>

    <div class="col-md-4">
        <label for="<?= $orderFormPrefix ?>_people" class="form-label">
            Nombre de personnes
        </label>
        <input type="number" id="<?= $orderFormPrefix ?>_people"
            name="people_count" class="form-control js-order-people"
            min="<?= (int) $menu['min_people'] ?>" max="1000"
            value="<?= htmlspecialchars($form['people_count']) ?>" required>
    </div>
</div>

<section class="order-price-summary mt-4"
    aria-labelledby="<?= $orderFormPrefix ?>_price_title"
    data-base-price="<?= (float) $menu['base_price'] ?>"
    data-min-people="<?= (int) $menu['min_people'] ?>">
    <h2 id="<?= $orderFormPrefix ?>_price_title" class="h5">Détail du prix</h2>
    <dl class="row mb-0">
        <dt class="col-sm-7">Prix du menu</dt>
        <dd class="col-sm-5 text-sm-end js-menu-price">
            <?= $pricing === null
                ? 'À calculer'
                : number_format($pricing['menu_price'], 2, ',', ' ') . ' €' ?>
        </dd>

        <dt class="col-sm-7">Livraison</dt>
        <dd class="col-sm-5 text-sm-end js-delivery-price">
            <?= $pricing === null
                ? 'À calculer'
                : number_format($pricing['delivery_price'], 2, ',', ' ') . ' €' ?>
        </dd>

        <dt class="col-sm-7">Réduction de 10 %</dt>
        <dd class="col-sm-5 text-sm-end js-discount">
            <?= $pricing === null
                ? 'À calculer'
                : '- ' . number_format($pricing['discount_amount'], 2, ',', ' ') . ' €' ?>
        </dd>

        <dt class="col-sm-7 border-top pt-2">Total</dt>
        <dd class="col-sm-5 text-sm-end border-top pt-2 fw-bold js-total-price">
            <?= $pricing === null
                ? 'À calculer'
                : number_format($pricing['total_price'], 2, ',', ' ') . ' €' ?>
        </dd>
    </dl>
    <p class="form-text mb-0">
        La réduction s’applique à partir de cinq personnes au-dessus du minimum.
        La livraison est offerte à Bordeaux et facturée 5 € ailleurs.
    </p>
</section>
