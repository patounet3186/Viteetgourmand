<section class="section">
    <h1>Gestion des menus</h1>
    <p>Consultez et mettez à jour les menus proposés aux clients.</p>
      <?php if ($menuCreated): ?>
          <div class="alert alert-success js-auto-hide">
              Le menu a bien été ajouté.
          </div>
      <?php endif; ?>
      <?php if ($menuUpdated): ?>
          <div class="alert alert-success js-auto-hide">
              Le stock et la visibilité du menu ont été mis à jour.
          </div>
      <?php endif; ?>

      <?php if (!empty($menuErrors)): ?>
          <div class="alert alert-danger" role="alert">
              <ul class="mb-0">
                  <?php foreach ($menuErrors as $error): ?>
                      <li><?= htmlspecialchars($error) ?></li>
                  <?php endforeach; ?>
              </ul>
          </div>
      <?php endif; ?>

      <h2 class="h3 mt-5">Ajouter un menu</h2>

      <form method="post" class="card p-4 mb-4">
          <input type="hidden" name="action" value="create_menu">
          <?= csrf_field() ?>

          <div class="row g-3">
              <div class="col-md-6">
                  <label for="title" class="form-label">Nom du menu</label>
                  <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($formMenu['title']) ?>" required>
              </div>

              <div class="col-md-6">
                  <label for="theme" class="form-label">Thème</label>
                  <input type="text" id="theme" name="theme" class="form-control" value="<?= htmlspecialchars($formMenu['theme']) ?>" required>
              </div>

              <div class="col-md-6">
                  <label for="diet" class="form-label">Régime</label>
                  <select id="diet" name="diet" class="form-select" required>
                      <option value="classique" <?= $formMenu['diet'] === 'classique' ? 'selected' : '' ?>>Classique</option>
                      <option value="végétarien" <?= $formMenu['diet'] === 'végétarien' ? 'selected' : '' ?>>Végétarien</option>
                      <option value="végan" <?= $formMenu['diet'] === 'végan' ? 'selected' : '' ?>>Végan</option>
                  </select>
              </div>

              <div class="col-md-6">
                  <label for="min_people" class="form-label">Nombre minimum de personnes</label>
                  <input type="number" id="min_people" name="min_people" min="1" class="form-control" value="<?= htmlspecialchars($formMenu['min_people']) ?>" required>
              </div>

              <div class="col-md-6">
                  <label for="base_price" class="form-label">Prix de base</label>
                  <input type="number" id="base_price" name="base_price" min="0.01" step="0.01" class="form-control" value="<?= htmlspecialchars($formMenu['base_price']) ?>" required>
              </div>

              <div class="col-md-6">
                  <label for="stock" class="form-label">Stock disponible</label>
                  <input type="number" id="stock" name="stock" min="0" class="form-control" value="<?= htmlspecialchars($formMenu['stock']) ?>" required>
              </div>

              <div class="col-12">
                  <label for="description" class="form-label">Description</label>
                  <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($formMenu['description']) ?></textarea>
              </div>

              <div class="col-12">
                  <label for="conditions_text" class="form-label">Conditions</label>
                  <textarea id="conditions_text" name="conditions_text" class="form-control" rows="3" required><?= htmlspecialchars($formMenu['conditions_text']) ?></textarea>
              </div>

              <div class="col-12">
                  <label for="image_url" class="form-label">Chemin de l’image, facultatif</label>
                  <input type="text" id="image_url" name="image_url" class="form-control"
                      value="<?= htmlspecialchars($formMenu['image_url']) ?>"
                      placeholder="public/images/mon-menu.webp">
              </div>
          </div>
          <div class="form-check mt-3">
              <input
                  class="form-check-input"
                  type="checkbox"
                  id="is_active"
                  name="is_active"
                  value="1"
                  <?= $formMenu['is_active'] === 1? 'checked': '' ?>
              >
              <label class="form-check-label" for="is_active">
                  Rendre ce menu visible aux clients
              </label>
          </div>

          <button type="submit" class="btn-app">Ajouter le menu</button>
      </form>
      <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Menu</th>
            <th>Thème</th>
            <th>Régime</th>
            <th>Minimum</th>
            <th>Prix</th>
            <th>Stock / Visibilité</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($menus as $menu): ?>
            <tr>
              <td><?= htmlspecialchars($menu['title']) ?></td>
              <td><?= htmlspecialchars($menu['theme']) ?></td>
              <td><?= htmlspecialchars($menu['diet']) ?></td>
              <td><?= (int) $menu['min_people'] ?> personnes</td>
              <td><?= number_format((float) $menu['base_price'], 2, ',', ' ') ?> €</td>

              <td>
                <form method="post" class="d-flex gap-2 align-items-center menu-management-form">
                    <input type="hidden" name="action" value="update_menu_state">
                    <input type="hidden" name="menu_id" value="<?= (int) $menu['id'] ?>">
                    <?= csrf_field() ?>

                    <input
                        type="number"
                        name="stock"
                        min="0"
                        class="form-control form-control-sm menu-stock-input"
                        value="<?= (int) $menu['stock'] ?>"
                        aria-label="Stock"
                    >

                    <select name="is_active" class="form-select form-select-sm menu-status-select" aria-label="Visibilité">
                        <option value="1" <?= (int) $menu['is_active'] === 1 ? 'selected' : '' ?>>
                            Actif
                        </option>
                        <option value="0" <?= (int) $menu['is_active'] === 0 ? 'selected' : '' ?>>
                            Inactif
                        </option>
                    </select>

                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                        Mettre à jour
                    </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
</section>
