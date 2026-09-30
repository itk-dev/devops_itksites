import "./styles/admin.css";
import TomSelect from "tom-select";

const tomSelectHandler = () => {
  document.querySelectorAll("[data-tom-select-settings]").forEach((el) => {
    // Don't do anything if element has already been tomselected.
    if (el.classList.contains("tomselected")) {
      return;
    }

    try {
      // @see https://tom-select.js.org/docs/
      const settings = JSON.parse(el.getAttribute("data-tom-select-settings"));
      new TomSelect(el, settings);
    } catch (exception) {}
  });
};

window.addEventListener("DOMContentLoaded", tomSelectHandler);
document.addEventListener("ea.collection.item-added", tomSelectHandler);

// Copy the text of a [data-copy-text] button to the clipboard, and show a
// check mark for a moment, like GitHub's copy buttons.
document.addEventListener("click", async (event) => {
  const button = event.target.closest("[data-copy-text]");
  if (!button) {
    return;
  }

  await navigator.clipboard.writeText(button.dataset.copyText);
  const icon = button.querySelector("i");
  icon.className = "fas fa-check text-success";
  setTimeout(() => (icon.className = "far fa-copy"), 2000);
});
