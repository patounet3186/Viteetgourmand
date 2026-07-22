# Charte graphique - Vite & Gourmand

## Positionnement

L’interface traduit un service traiteur artisanal, fiable et chaleureux. Le
contenu culinaire reste central : photographies lisibles, informations de
commande directes et back-office dense sans surcharge décorative.

## Palette

| Usage | Couleur | Code |
| --- | --- | --- |
| Texte, navigation, pied de page | Vert forêt | `#18352f` |
| Fond secondaire | Vert très clair | `#eef4ef` |
| Fond général | Blanc cassé | `#f8faf7` |
| Action principale | Rouge bordeaux | `#b83232` |
| Survol action principale | Bordeaux foncé | `#952727` |
| Bordures | Vert grisé | `#dce7df` |
| Surfaces | Blanc | `#ffffff` |
| Focus clavier | Bleu | `#0b6bcb` |

Le rouge est réservé aux actions majeures, aux prix et aux alertes. Le vert
porte l’identité et maintient un contraste élevé sur les surfaces claires.

## Typographie

```css
font-family: "Segoe UI", Arial, sans-serif;
```

La pile système évite un téléchargement de police et reste lisible sur Windows,
macOS, Android et iOS.

| Élément | Taille indicative | Graisse |
| --- | --- | --- |
| Titre du héros ordinateur | `48px` | forte |
| Titre du héros mobile | `36px` | forte |
| Titre de page | Bootstrap `h1` | forte |
| Texte courant | `16px` | normale |
| Bouton | `16px` | `700` |

L’espacement des lettres reste nul. Les textes reviennent à la ligne avant
d’être réduits.

## Photographies

- format WebP pour limiter le poids ;
- sujet culinaire identifiable et correctement exposé ;
- `object-fit: cover` dans les galeries ;
- voile vert foncé sur les cartes pour garantir la lecture du texte ;
- texte alternatif décrivant le menu lorsque l’image porte une information.

Les photographies proviennent de Pexels et sont conservées localement.

## Composants

- barre de navigation Bootstrap, repliée en bouton sur mobile ;
- bouton principal rouge, rayon de `6px` ;
- cartes limitées aux menus, plats, avis et indicateurs répétés ;
- formulaires avec label visible, aide courte et message d’erreur ;
- tableaux fins proches d’un tableur, largeur adaptée au contenu ;
- alertes temporaires avec la classe `js-auto-hide` ;
- badges chiffrés pour les commandes, avis et notifications.

## Mise en page

- sections publiques avec `56px 8%` sur ordinateur ;
- sections mobiles avec `36px 20px` ;
- grille de trois menus sur ordinateur, une colonne sous `900px` ;
- fiche menu en deux colonnes, puis une colonne sous `900px` ;
- rayon maximal de `6px` pour garder une apparence sobre ;
- pied de page toujours après le contenu, même sur les pages courtes.

## Responsive

Les références de contrôle sont :

- ordinateur : `1440 × 1000` ;
- mobile : `390 × 844`.

Sous `576px`, le héros, la navigation, le pied de page et les galeries sont
adaptés. Les tableaux du back-office conservent leurs colonnes et deviennent
défilables horizontalement.

## Accessibilité

- langue du document définie en français ;
- lien d’évitement vers le contenu principal ;
- focus clavier bleu de trois pixels ;
- titres hiérarchisés et régions `header`, `main`, `footer`, `nav` ;
- labels associés aux champs ;
- messages d’état avec `role="status"` ou `role="alert"` ;
- texte caché complétant la signification des badges ;
- commandes explicites, sans dépendre uniquement de la couleur ;
- contrastes renforcés sur les images par un voile sombre.

Une vérification manuelle au clavier et avec l’outil Lighthouse doit compléter
la revue avant la soutenance.

## Maquettes

Les wireframes et les six captures finales sont référencés dans
[`maquettes.md`](maquettes.md).
