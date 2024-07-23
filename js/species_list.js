function edit_species() {
 var species_id = document.getElementById('species_id').value;
 if (species_id) {
  var u = 'species_info.php?id=' + species_id;
  window.open(u);
 }
}

function clear_species() {
 document.getElementById('species_id').value = 0;
 document.getElementById('species_id_display').value = '';
}