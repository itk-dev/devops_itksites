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
