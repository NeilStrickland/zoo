function delete_sound(sound_id) {
 var x = new XMLHttpRequest();
 var u = 'ajax/delete_sound.php' +
         '?sound_id=' + sound_id;
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
