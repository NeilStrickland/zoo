zoo.object.ajax_url = '//' + location.hostname + '/zoo/ajax';
zoo.object.send_url = '//' + location.hostname + '/zoo/send';

zoo.photo.url = function() {
 return this.send_url + '/send_photo.php?id=' + this.id;
}

zoo.photo.img = function() {
 return '<img src="' + this.url() + '"/>';
}

zoo.photo.small_img = function() {
 return '<img width="180" src="' + this.url() + '"/>';
}

zoo.photo.tiny_img = function() {
 return '<img width="90" src="' + this.url() + '"/>';
}
