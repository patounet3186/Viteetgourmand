<div class="row g-3">
    <div class="col-md-6">
        <label for="<?= $formIdPrefix ?>_title" class="form-label">Nom du menu</label>
        <input type="text" id="<?= $formIdPrefix ?>_title" name="title"
            class="form-control" minlength="3" maxlength="150"
            value="<?= htmlspecialchars($formMenu['title']) ?>" required>
    </div>

    <div class="col-md-6">
        <label for="<?= $formIdPrefix ?>_theme" class="form-label">Thème</label>
        <input type="text" id="<?= $formIdPrefix ?>_theme" name="theme"
            class="form-control" minlength="2" maxlength="80"
            value="<?= htmlspecialchars($formMenu['theme']) ?>" required>
    </div>

    <div class="col-md-4">
        <label for="<?= $formIdPrefix ?>_diet" class="form-label">Régime</label>
        <select id="<?= $formIdPrefix ?>_diet" name="diet" class="form-select" required>
            <option value="classique" <?= $formMenu['diet'] === 'classique' ? 'selected' : '' ?>>Classique</option>
            <option value="végétarien" <?= $formMenu['diet'] === 'végétarien' ? 'selected' : '' ?>>Végétarien</option>
            <option value="végan" <?= $formMenu['diet'] === 'végan' ? 'selected' : '' ?>>Végan</option>
        </select>
    </div>

    <div class="col-md-4">
        <label for="<?= $formIdPrefix ?>_min_people" class="form-label">
            Nombre minimum de personnes
        </label>
        <input type="number" id="<?= $formIdPrefix ?>_min_people" name="min_people"
            class="form-control" min="1"
            value="<?= htmlspecialchars($formMenu['min_people']) ?>" required>
    </div>

    <div class="col-md-4">
        <label for="<?= $formIdPrefix ?>_base_price" class="form-label">
            Prix pour le minimum
        </label>
        <input type="number" id="<?= $formIdPrefix ?>_base_price" name="base_price"
            class="form-control" min="0.01" step="0.01"
            value="<?= htmlspecialchars($formMenu['base_price']) ?>" required>
    </div>

    <div class="col-md-4">
        <label for="<?= $formIdPrefix ?>_stock" class="form-label">
            Commandes encore disponibles
        </label>
        <input type="number" id="<?= $formIdPrefix ?>_stock" name="stock"
            class="form-control" min="0"
            value="<?= htmlspecialchars($formMenu['stock']) ?>" required>
    </div>

    <div class="col-12">
        <label for="<?= $formIdPrefix ?>_description" class="form-label">Description</label>
        <textarea id="<?= $formIdPrefix ?>_description" name="description"
            class="form-control" minlength="10" maxlength="2000"
            rows="4" required><?= htmlspecialchars($formMenu['description']) ?></textarea>
    </div>

    <div class="col-12">
        <label for="<?= $formIdPrefix ?>_conditions" class="form-label">
            Conditions de commande et de conservation
        </label>
        <textarea id="<?= $formIdPrefix ?>_conditions" name="conditions_text"
            class="form-control" minlength="10" maxlength="2000"
            rows="3" required><?= htmlspecialchars($formMenu['conditions_text']) ?></textarea>
    </div>

    <div class="col-12">
        <label for="<?= $formIdPrefix ?>_images" class="form-label">
            Galerie d’images
        </label>
        <textarea id="<?= $formIdPrefix ?>_images" name="image_urls"
            class="form-control" rows="4" required
            aria-describedby="<?= $formIdPrefix ?>_images_help"
        ><?= htmlspecialchars($formMenu['image_urls']) ?></textarea>
        <div id="<?= $formIdPrefix ?>_images_help" class="form-text">
            Une adresse par ligne, six images maximum. Exemple :
            public/images/menu-noel.webp
        </div>
    </div>
</div>

<fieldset class="mt-4">
    <legend class="h5">Composition du menu</legend>
    <p class="text-muted">Sélectionnez au moins une entrée, un plat et un dessert.</p>

    <?php foreach ($categoryLabels as $category => $categoryLabel): ?>
        <div class="mb-3">
            <p class="fw-bold mb-2"><?= htmlspecialchars($categoryLabel) ?></p>
            <div class="row g-2">
                <?php foreach ($dishes as $dish): ?>
                    <?php if ($dish['category'] === $category): ?>
                        <?php $dishId = (int) $dish['id']; ?>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input"
                                    id="<?= $formIdPrefix ?>_dish_<?= $dishId ?>"
                                    name="dish_ids[]" value="<?= $dishId ?>"
                                    <?= in_array($dishId, $formMenu['dish_ids'], true) ? 'checked' : '' ?>>
                                <label class="form-check-label"
                                    for="<?= $formIdPrefix ?>_dish_<?= $dishId ?>">
                                    <?= htmlspecialchars($dish['name']) ?>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</fieldset>

<div class="form-check mt-3">
    <input class="form-check-input" type="checkbox"
        id="<?= $formIdPrefix ?>_is_active" name="is_active" value="1"
        <?= (int) $formMenu['is_active'] === 1 ? 'checked' : '' ?>>
    <label class="form-check-label" for="<?= $formIdPrefix ?>_is_active">
        Rendre ce menu visible et commandable
    </label>
</div>
