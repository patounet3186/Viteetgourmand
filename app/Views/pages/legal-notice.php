<section class="section legal-content">
    <h1>Mentions légales</h1>

    <h2>Éditeur du site</h2>
    <p>
        Le site Vite & Gourmand est édité par
        <?= htmlspecialchars($companyLegalName) ?>, traiteur représenté par
        Julie et José.
    </p>
    <p>Adresse : <?= htmlspecialchars($companyAddress) ?>.</p>
    <?php if ($companySiret !== ''): ?>
        <p>SIRET : <?= htmlspecialchars($companySiret) ?>.</p>
    <?php endif; ?>
    <p>
        Contact :
        <a href="mailto:<?= htmlspecialchars($companyEmail, ENT_QUOTES) ?>">
            <?= htmlspecialchars($companyEmail) ?>
        </a>
    </p>

    <h2>Responsable de la publication</h2>
    <p>Julie et José, dirigeants de Vite & Gourmand.</p>

    <h2>Hébergement</h2>
    <p>
        L’application et sa base relationnelle sont hébergées par alwaysdata,
        91 rue du Faubourg Saint-Honoré, 75008 Paris, France.
    </p>

    <h2>Propriété intellectuelle</h2>
    <p>
        Les textes, éléments graphiques et photographies du site sont protégés.
        Toute reproduction non autorisée est interdite. Les photographies issues
        de Pexels sont utilisées conformément à leur licence.
    </p>

    <h2>Responsabilité</h2>
    <p>
        Vite & Gourmand veille à l’exactitude des informations publiées.
        Les allergènes et conditions propres à chaque menu doivent être consultés
        avant toute commande.
    </p>
</section>
