function add_image(species_id,i) {
 var td  = document.getElementById('image_td_'  + i);
 var img = document.getElementById('image_img_' + i);
 var url = img.src;

 var x = new XMLHttpRequest();
 var u = 'ajax/add_image.php' +
         '?species_id=' + species_id + 
         '&url=' + encodeURIComponent(url);
 try {
  x.open('GET',u,false);
 } catch(e) {
  alert('XHR could not connect');
 }

 try {
  x.send(null);
 } catch(e) {
  alert('XHR send failed');
 }

 td.style.display = 'none';
}

function delete_image(image_id) {
 var x = new XMLHttpRequest();
 var u = 'ajax/delete_image.php' +
         '?image_id=' + image_id;
 try {
  x.open('GET',u,false);
 } catch(e) {
  alert('XHR could not connect');
 }

 try {
  x.send(null);
 } catch(e) {
  alert('XHR send failed');
 }
}
