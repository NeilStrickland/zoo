function delete_sound(sound_id) {
 var x = new XMLHttpRequest();
 var u = 'ajax/delete_sound.php' +
         '?sound_id=' + sound_id;

 fetch(u)
  .then(response => response.text())
  .then(data => {
    document.getElementById('deleter_td_' + sound_id).innerHTML = 'Deleted';
  });
}
