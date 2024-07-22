function remove_membership(id) {
 var tr = document.getElementById('quiz_group_membership_tr_' + id);
 var x = frog.create_xhr();
 var u = 'ajax/delete_quiz_group_membership.php?id=' + id;
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

 tr.style.display = 'none';
}

function find_images(i,g,s) {
 window.open('find_images.php?id=' + i,'Find images');
 var u;
 u = "https://www.google.com/search?hl=en&q=";
 u += g + "+" + s;
 u += "&btnG=Search+Images&gbv=2&tbm=isch&tbs=isz:l";
 window.open(u,'Google images');
}

function find_sounds(i,g,s) {
 var u = 'https://xeno-canto.org/explore?view=0&query=' + g + '+' + s;
 window.open(u,'Xeno-canto');
 window.open('find_sounds.php?id=' + i,'Find sounds');
}

