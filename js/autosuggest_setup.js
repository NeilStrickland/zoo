try {
 if (window.loaded_scripts &&
     window.loaded_scripts.autosuggest &&
     autosuggest.setup_all) {
  window.autosuggest_inputs = autosuggest.setup_all();
 }
} catch(e) {}

