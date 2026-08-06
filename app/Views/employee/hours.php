<section class="section">
    <h1>Gestion des horaires</h1>
    <p>Ces horaires sont affichés automatiquement dans le pied de page.</p>

    <?php if ($hoursUpdated): ?>
        <div class="alert alert-success js-auto-hide" role="status">
            Les horaires ont été mis à jour.
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

    <form method="post" class="card p-4">
        <input type="hidden" name="action" value="update_hours">
        <?= csrf_field() ?>

        <div class="table-responsive">
            <table class="table align-middle">
                <caption class="visually-hidden">Horaires d’ouverture par jour</caption>
                <thead>
                    <tr>
                        <th scope="col">Jour</th>
                        <th scope="col">Ouverture</th>
                        <th scope="col">Fermeture</th>
                        <th scope="col">Réouverture</th>
                        <th scope="col">Fermeture</th>
                        <th scope="col">Fermé</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $hourFieldLabels = [
                        'first_open' => 'début de la première plage',
                        'first_close' => 'fin de la première plage',
                        'second_open' => 'début de la seconde plage',
                        'second_close' => 'fin de la seconde plage',
                    ];
                    ?>
                    <?php foreach ($hours as $day): ?>
                        <?php $dayNumber = (int) $day['day_of_week']; ?>
                        <tr>
                            <th scope="row"><?= htmlspecialchars($day['day_label']) ?></th>
                            <?php foreach (['first_open', 'first_close', 'second_open', 'second_close'] as $field): ?>
                                <td>
                                    <input
                                        type="time"
                                        name="<?= $field ?>[<?= $dayNumber ?>]"
                                        class="form-control form-control-sm hours-input"
                                        value="<?= htmlspecialchars(substr((string) ($day[$field] ?? ''), 0, 5)) ?>"
                                        aria-label="<?= htmlspecialchars(
                                            $day['day_label'] . ' - ' . $hourFieldLabels[$field]
                                        ) ?>"
                                    >
                                </td>
                            <?php endforeach; ?>
                            <td>
                                <input
                                    type="checkbox"
                                    name="is_closed[<?= $dayNumber ?>]"
                                    class="form-check-input"
                                    value="1"
                                    aria-label="<?= htmlspecialchars($day['day_label']) ?> fermé"
                                    <?= (int) $day['is_closed'] === 1 ? 'checked' : '' ?>
                                >
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary align-self-start mt-3">
            Enregistrer les horaires
        </button>
    </form>
</section>
