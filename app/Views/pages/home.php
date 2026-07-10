<?php

require_once __DIR__ . '/../../Services/reviews.php';

$reviews = array_slice(getReviewsByStatus('validated'), 0, 3);
?>

<section class="hero">
  <div>
    <h1>Des menus traiteur pour vos moments importants</h1>
    <p>
      Julie et José accompagnent les particuliers et professionnels à Bordeaux
      depuis 25 ans avec des menus faits maison.
    </p>
    <a class="btn-app" href="?page=menus">Voir les menus</a>
  </div>
</section>

<section class="section">
  <h2>Bienvenue chez Vite & Gourmand</h2>
  <p>
    Notre équipe propose des menus adaptés aux repas de famille,
    événements professionnels et célébrations.
  </p>
</section>

<section class="section reviews-section">
    <h2>Les avis de nos clients</h2>
    <p>Découvrez les retours de clients ayant commandé chez Vite & Gourmand.</p>

    <?php if (empty($reviews)): ?>
        <p class="text-muted">Les premiers avis validés seront bientôt affichés ici.</p>
    <?php else: ?>
        <div class="row g-4 mt-2">
            <?php foreach ($reviews as $review): ?>
                <div class="col-md-4">
                    <article class="card h-100 review-card">
                        <div class="card-body">
                            <p class="review-rating">
                                Note : <?= (int) $review['rating'] ?>/5
                            </p>

                            <blockquote class="mb-3">
                                <?= htmlspecialchars($review['comment']) ?>
                            </blockquote>

                            <p class="mb-0 fw-bold">
                                <?= htmlspecialchars($review['user_name']) ?>
                            </p>

                            <p class="text-muted mb-0">
                                <?= htmlspecialchars($review['menu_title']) ?>
                            </p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>