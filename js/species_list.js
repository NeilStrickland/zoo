function edit_species() {
 var species_id = document.getElementById('species_id').value;
 var u = 'species_info.php?id=' + species_id;
 window.open(u);
}