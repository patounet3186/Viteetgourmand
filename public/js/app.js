const autoHideMessages = document.querySelectorAll('.js-auto-hide');

autoHideMessages.forEach((message) => {
  setTimeout(() => {
    message.classList.add('fade-out');

    setTimeout(() => {
      message.remove();
    }, 400);
  }, 3000);
});