function edit_taxon() {
 var taxon_id = document.getElementById('taxon_id').value;
 var u = 'taxon_info.php?id=' + taxon_id;
 window.open(u);
}