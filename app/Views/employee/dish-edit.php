<section class="section">
  <h1>Modifier un plat</h1>
  <p>
    Modifiez les informations de
    <strong><?= htmlspecialchars($dish['name']) ?></strong>.
  </p>

  <?php if (!empty($editErrors)): ?>
    <div class="alert alert-danger" role="alert">
      <ul class="mb-0">
        <?php foreach ($editErrors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form
    method="post"
    action="?page=employee-dish-edit&amp;id=<?= (int) $dishId ?>"
    class="card p-4 mt-4"
  >
    <input type="hidden" name="action" value="update_dish">
    <?= csrf_field() ?>

    <div class="row g-3">
      <div class="col-md-6">
        <label for="name" class="form-label">Nom du plat</label>
        <input
          type="text"
          id="name"
          name="name"
          class="form-control"
          minlength="2"
          maxlength="150"
          value="<?= htmlspecialchars($formDish['name']) ?>"
          required
        >
      </div>

      <div class="col-md-6">
        <label for="category" class="form-label">Catégorie</label>
        <select id="category" name="category" class="form-select" required>
          <option value="entree" <?= $formDish['category'] === 'entree' ? 'selected' : '' ?>>
            Entrée
          </option>
          <option value="plat" <?= $formDish['category'] === 'plat' ? 'selected' : '' ?>>
            Plat
          </option>
          <option value="dessert" <?= $formDish['category'] === 'dessert' ? 'selected' : '' ?>>
            Dessert
          </option>
        </select>
      </div>

      <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea
          id="description"
          name="description"
          class="form-control"
          minlength="10"
          maxlength="1000"
          rows="4"
          required
        ><?= htmlspecialchars($formDish['description']) ?></textarea>
      </div>

      <div class="col-12">
        <label for="allergens" class="form-label">Allergènes éventuels</label>
        <input
          type="text"
          id="allergens"
          name="allergens"
          class="form-control"
          maxlength="255"
          value="<?= htmlspecialchars($formDish['allergens']) ?>"
        >
      </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
      <button type="submit" class="btn btn-primary">
        Enregistrer les modifications
      </button>

      <a href="?page=employee-dishes" class="btn btn-outline-secondary">
        Annuler
      </a>
    </div>
  </form>
</section>
