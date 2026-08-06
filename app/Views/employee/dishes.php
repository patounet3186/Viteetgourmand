<section class="section">
  <h1>Gestion des plats</h1>
  <p>Créez et gérez les plats pouvant être associés aux menus.</p>

  <?php if ($dishCreated): ?>
  <div class="alert alert-success js-auto-hide">
    Le plat a bien été ajouté.
  </div>
<?php endif; ?>

<?php if ($dishUpdated): ?>
  <div class="alert alert-success js-auto-hide">
    Le plat a bien été modifié.
  </div>
<?php endif; ?>

<?php if ($dishDeleted): ?>
  <div class="alert alert-success js-auto-hide" role="status">
    Le plat a bien été supprimé.
  </div>
<?php endif; ?>

<?php if (!empty($dishErrors)): ?>
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">
      <?php foreach ($dishErrors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<h2 class="h3 mt-5">Ajouter un plat</h2>

<form method="post" class="card p-4 mb-4">
  <input type="hidden" name="action" value="create_dish">
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
        rows="3"
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

  <button type="submit" class="btn btn-primary mt-3">
    Ajouter le plat
  </button>
</form>

<h2 class="h3 mt-5">Plats enregistrés</h2>
  <?php if (empty($dishes)): ?>
    <div class="alert alert-info mt-4">
      Aucun plat n'est encore enregistré.
    </div>
  <?php else: ?>
    <div class="table-responsive mt-4">
      <table class="table table-striped align-middle">
        <caption class="visually-hidden">Liste des plats disponibles</caption>

        <thead>
          <tr>
            <th scope="col">Plat</th>
            <th scope="col">Catégorie</th>
            <th scope="col">Description</th>
            <th scope="col">Allergènes</th>
            <th scope="col">Menus</th>
            <th scope="col">Gestion</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($dishes as $dish): ?>
            <tr>
              <td><?= htmlspecialchars($dish['name']) ?></td>
              <td>
                <?= htmlspecialchars($categoryLabels[$dish['category']] ?? $dish['category']) ?>
              </td>
              <td class="table-cell-wrap">
                <?= htmlspecialchars($dish['description'] ?: 'Non renseignée') ?>
              </td>
              <td class="table-cell-wrap">
                <?= htmlspecialchars($dish['allergens'] ?: 'Aucun allergène renseigné') ?>
              </td>
              <td><?= (int) $dish['menus_count'] ?></td>
              <td>
                <div class="d-flex gap-2">
                  <a
                    href="?page=employee-dish-edit&id=<?= (int) $dish['id'] ?>"
                    class="btn btn-sm btn-primary"
                  >
                    Modifier
                  </a>
                  <?php if ((int) $dish['menus_count'] === 0): ?>
                    <form method="post" class="d-inline js-confirm-form"
                      data-confirm="Supprimer définitivement ce plat ?">
                      <input type="hidden" name="action" value="delete_dish">
                      <input type="hidden" name="dish_id" value="<?= (int) $dish['id'] ?>">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        Supprimer
                      </button>
                    </form>
                  <?php else: ?>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary"
                      title="Retirez d’abord ce plat de tous les menus"
                      disabled
                    >
                      Supprimer
                    </button>
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
