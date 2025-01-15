document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll(".tab-button");
  const panes = document.querySelectorAll(".tab-pane");

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      // Retirer l'état actif de tous les boutons et contenu
      buttons.forEach((btn) => btn.classList.remove("active"));
      panes.forEach((pane) => pane.classList.remove("active"));

      // Ajouter l'état actif au bouton et au contenu correspondant
      button.classList.add("active");
      const tabId = button.getAttribute("data-tab");
      document.getElementById(tabId).classList.add("active");
    });
  });
});
