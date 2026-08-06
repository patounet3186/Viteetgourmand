<section class="section">
    <h1>Gestion des accès employés</h1>
    <p class="text-muted">
        Seuls les comptes internes nécessaires à la gestion des accès sont
        affichés sur cette page.
    </p>
    <?php if ($userUpdated): ?>
        <div class="alert alert-success js-auto-hide">Utilisateur mis à jour.</div>
    <?php endif; ?>

    <?php if ($csrfError): ?>
        <div class="alert alert-danger js-auto-hide">Le formulaire a expiré, merci de réessayer.</div>
    <?php endif; ?>
    <?php if ($employeeCreated): ?>
        <div class="alert alert-success js-auto-hide">
            Le compte employé a bien été créé.
        </div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
    <h2 class="h3 mt-4">Créer un employé</h2>

    <form method="post" class="card p-4 mb-4">
        <input type="hidden" name="action" value="create_employee">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="employee_first_name" class="form-label">Prénom</label>
                <input
                    type="text"
                    id="employee_first_name"
                    name="first_name"
                    class="form-control"
                    value="<?= htmlspecialchars($employeeForm['first_name']) ?>"
                    maxlength="100"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="employee_last_name" class="form-label">Nom</label>
                <input
                    type="text"
                    id="employee_last_name"
                    name="last_name"
                    class="form-control"
                    value="<?= htmlspecialchars($employeeForm['last_name']) ?>"
                    maxlength="100"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="employee_email" class="form-label">Adresse e-mail</label>
                <input
                    type="email"
                    id="employee_email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($employeeForm['email']) ?>"
                    maxlength="180"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="employee_password" class="form-label">Mot de passe temporaire</label>
                <input
                    type="password"
                    id="employee_password"
                    name="password"
                    class="form-control"
                    minlength="10"
                    maxlength="72"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="employee_is_active" class="form-label">État du compte</label>
                <select
                    id="employee_is_active"
                    name="is_active"
                    class="form-select"
                    required
                >
                    <option value="1" <?= $employeeForm['is_active'] === '1' ? 'selected' : '' ?>>
                        Actif
                    </option>
                    <option value="0" <?= $employeeForm['is_active'] === '0' ? 'selected' : '' ?>>
                        Inactif
                    </option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn-app">Créer l’employé</button>
    </form>

    <h2 class="h3">Comptes existants</h2>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>État du compte</th>
                    <th>Créé le</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php $isCurrentUser = (int) $user['id'] === $currentUserId; ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                            <?php if ($isCurrentUser): ?>
                                <span class="badge text-bg-secondary">Compte actuel</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($roles[$user['role']] ?? $user['role']) ?></td>
                        <td>
                            <?= (int) $user['is_active'] === 1 ? 'Actif' : 'Inactif' ?>
                        </td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td>
                            <?php if ($user['role'] === 'employee'): ?>
                                <form method="post" class="d-flex gap-2">
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="update_employee_status"
                                    >

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?= (int) $user['id'] ?>"
                                    >

                                    <select
                                        name="is_active"
                                        class="form-select form-select-sm"
                                        aria-label="État du compte employé"
                                    >
                                        <option value="1" <?= (int) $user['is_active'] === 1 ? 'selected' : '' ?>>
                                            Actif
                                        </option>
                                        <option value="0" <?= (int) $user['is_active'] === 0 ? 'selected' : '' ?>>
                                            Inactif
                                        </option>
                                    </select>

                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Mettre à jour
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Consultation uniquement</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
