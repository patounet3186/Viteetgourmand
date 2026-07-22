const minPriceInput = document.querySelector( '#filterMinPrice' );
const maxPriceInput = document.querySelector( '#filterMaxPrice' );
const themeSelect = document.querySelector( '#filterTheme' );
const dietSelect = document.querySelector( '#filterDiet' );
const peopleInput = document.querySelector( '#filterPeople' );
const menuCards = document.querySelectorAll( '.menu-card' );
const emptyMessage = document.querySelector( '#menuFilterEmpty' );

function filterMenus () {
  const minPrice = Number( minPriceInput?.value || 0 );
  const maxPrice = Number( maxPriceInput?.value || 0 );
  const selectedTheme = themeSelect?.value || '';
  const selectedDiet = dietSelect?.value || '';
  const people = Number( peopleInput?.value || 0 );

  let visibleCount = 0;

  menuCards.forEach( ( card ) => {
    const price = Number( card.dataset.price );
    const theme = card.dataset.theme;
    const diet = card.dataset.diet;
    const minPeople = Number( card.dataset.people );

    let isVisible = true;

    if ( minPrice > 0 && price < minPrice ) {
      isVisible = false;
    }

    if ( maxPrice > 0 && price > maxPrice ) {
      isVisible = false;
    }

    if ( selectedTheme !== '' && theme !== selectedTheme ) {
      isVisible = false;
    }

    if ( selectedDiet !== '' && diet !== selectedDiet ) {
      isVisible = false;
    }

    if ( people > 0 && minPeople > people ) {
      isVisible = false;
    }

    card.closest( '.col-md-4' ).style.display = isVisible ? '' : 'none';

    if ( isVisible ) {
      visibleCount++;
    }
  } );

  if ( emptyMessage ) {
    emptyMessage.hidden = visibleCount > 0;
  }
}

[ minPriceInput, maxPriceInput, themeSelect, dietSelect, peopleInput ].forEach( ( field ) => {
  field?.addEventListener( 'input', filterMenus );
  field?.addEventListener( 'change', filterMenus );
} );
