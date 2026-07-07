const maxPriceInput = document.querySelector( '#filterMaxPrice' );
const themeSelect = document.querySelector( '#filterTheme' );
const dietSelect = document.querySelector( '#filterDiet' );
const peopleInput = document.querySelector( '#filterPeople' );
const menuCards = document.querySelectorAll( '.menu-card' );

function filterMenus () {
  const maxPrice = Number( maxPriceInput?.value || 0 );
  const selectedTheme = themeSelect?.value || '';
  const selectedDiet = dietSelect?.value || '';
  const people = Number( peopleInput?.value || 0 );

  menuCards.forEach( ( card ) => {
    const price = Number( card.dataset.price );
    const theme = card.dataset.theme;
    const diet = card.dataset.diet;
    const minPeople = Number( card.dataset.people );

    let isVisible = true;

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
  } );
}

[ maxPriceInput, themeSelect, dietSelect, peopleInput ].forEach( ( field ) => {
  field?.addEventListener( 'input', filterMenus );
  field?.addEventListener( 'change', filterMenus );
} );