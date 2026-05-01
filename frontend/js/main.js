/* LocalMarché - JS minimaliste (chargé en defer)
   Une seule responsabilité : confirmation des actions destructrices.
   Aucune dépendance, aucun framework. */
(function () {
  'use strict';
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (f.dataset && f.dataset.confirm) {
      if (!window.confirm(f.dataset.confirm)) {
        e.preventDefault();
      }
    }
  });
})();
