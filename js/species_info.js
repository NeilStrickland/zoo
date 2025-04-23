init = function() {
 var genus_input = document.main_form.genus;
 var species_input = document.main_form.species;
 var common_name_input = document.main_form.common_name;
 var family_input = document.main_form.family;
 var order_input = document.main_form.order;
 var class_input = document.main_form.class;
 var phylum_input = document.main_form.phylum;
 var kingdom_input = document.main_form.kingdom;
 var search_button = document.getElementById('search_button');

 species_input.addEventListener('change', function() {
  var x = species_input.value;
  if (genus_input.value == "" && x.match(/^[A-Z][a-z]+ [a-z]+$/)) {
   genus_input.value = x.split(" ")[0];
   species_input.value = x.split(" ")[1];
  }
 }, false);

 genus_input.addEventListener('change', function() {
  var x = genus_input.value;
  if (species_input.value == "" && x.match(/^[A-Z][a-z]+ [a-z]+$/)) {
   genus_input.value = x.split(" ")[0];
   species_input.value = x.split(" ")[1];
  }
 }, false);

 search_button.addEventListener('click', function() {
  if (genus_input.value == "" && species_input.value == "" && common_name_input.value == "") {
   return;
  }
  var x = genus_input.value + " " + species_input.value + " " + common_name_input.value;
  var u = 'https://www.google.com/search?q=' + encodeURIComponent(x);
  var w = window.open(u, '_blank');
  if (x.match(/^[A-Z][a-z]+ [a-z]+$/)) {
   window.location.href = "/species/" + x.replace(/ /g, "_") + ".html";
  }
 }, false);
}