init = function() {
 var genus_input = document.main_form.genus;
 var species_input = document.main_form.species;
 var common_name_input = document.main_form.common_name;
 var family_input_display = document.main_form.family_display;
 var order_input_display = document.main_form.order_display;
 var class_input_display = document.main_form.class_display;
 var phylum_input_display = document.main_form.phylum_display;
 var kingdom_input_display = document.main_form.kingdom_display;
 var search_button = document.getElementById('search_button');

 var fill_taxa = function() {
  var u = 'ajax/fill_taxa.php?' + 
  'genus=' + encodeURIComponent(genus_input.value) + 
  '&family=' + encodeURIComponent(family_input_display.value) + 
  '&order=' + encodeURIComponent(order_input_display.value) + 
  '&class=' + encodeURIComponent(class_input_display.value) +
  '&phylum=' + encodeURIComponent(phylum_input_display.value) +
  '&kingdom=' + encodeURIComponent(kingdom_input_display.value);
  fetch(u).then(r => r.json()).then(function(data) {
   if (data.genus && ! genus_input.value) {
    genus_input.value = data.genus;
   }
   if (data.family && ! family_input_display.value) {
    family_input_display.value = data.family;
   }
   if (data.order && ! order_input_display.value) {
    order_input_display.value = data.order;
   }
   if (data.class && ! class_input_display.value) {
    class_input_display.value = data.class;
   }
   if (data.phylum && ! phylum_input_display.value) {
    phylum_input_display.value = data.phylum;
   }
   if (data.kingdom && ! kingdom_input_display.value) {
    kingdom_input_display.value = data.kingdom;
   }
  });
 };

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
  fill_taxa();
 }, false);

 family_input_display.addEventListener('change', function() {
  fill_taxa();
 }, false);

 order_input_display.addEventListener('change', function() {
  fill_taxa();
 }, false);

 class_input_display.addEventListener('change', function() {
  fill_taxa();
 }, false);

 phylum_input_display.addEventListener('change', function() {
  fill_taxa();
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