zoo.species.create_dom = function() {
 var me = this;
 this.button = document.createElement('button');
 this.button.className = 'species_button';
 this.button.innerHTML = this.genus + '<br/>' + this.species;
 this.button.addEventListener('click', function() {
  zoo.classifier.add_photo_species(me.id);
 }, false);
};

zoo.classifier = {};

zoo.classifier.init = function() {
 this.selected_photo_div = document.getElementById('selected_photo_div');
 this.selected_photo_img = document.getElementById('selected_photo_img');
 this.selected_photo_info = document.getElementById('selected_photo_info');
 this.recent_species_div = document.getElementById('recent_species_div');
 this.species_selector = document.getElementById('species_selector');

 this.selected_photo_info.style.height = '20px';

 this.max_recent_species = 10;

 this.photos = [];
 this.species_by_id = {};
 this.recent_species = [];
 var i = 0;
 for (var s0 of species0) {
  s = Object.create(zoo.species);
  s.id = s0[0];
  s.genus = s0[1];
  s.species = s0[2];
  s.common_name = s0[3];
  this.species_by_id[s.id] = s;
  if (i < this.max_recent_species) {
   s.create_dom();
   this.recent_species.push(s.id);
   this.recent_species_div.appendChild(s.button);
   i++;
  }
 }

 for(var p0 of photos0) {
  p = Object.create(zoo.photo);
  p.id = p0[0];
  p.dir = p0[1];
  p.file_name = p0[2];
  p.ignore = p0[3];
  p.species = [];
  p.species_by_id = {};
  for (var i = 4; i < p0.length; i++) {
   ps = this.create_photo_species(p.id, p0[i][1], p0[i][0]);
   p.species.push(ps);
   p.species_by_id[ps.species_id] = ps;
  }
  p.ignore_box = document.createElement('input');
  p.ignore_box.type = 'checkbox';
  p.ignore_box.checked = p.ignore;
  (function(p0) {
   p0.ignore_box.addEventListener('click', function() {
    p0.ignore = p0.ignore_box.checked;
    p0.save();
   }, false);
  })(p);
  this.photos.push(p);
 } 
 this.num_photos = this.photos.length;

 this.selected_i = 0;
 this.selected_photo = null;


 document.body.addEventListener('keydown', function(e) {
  if (e.key == "ArrowLeft") {
   zoo.classifier.select_previous();
  } else if (e.key == "ArrowRight") {
   zoo.classifier.select_next();
  } else if (e.key == "#") {
   zoo.classifier.toggle_ignore();
  }
 }, false);

 autosuggest.onUseSuggestion = function() {
  var i = parseInt(this.key_elem.value);
  if (i != 0) {
   zoo.classifier.add_recent_species(i);
  }
  this.elem.value = '';
  this.key_elem.value = '';
 };

 this.select_photo(0);
}

zoo.classifier.select_photo = function(i) {
 this.selected_i = i;
 this.selected_photo = this.photos[i];
 this.selected_photo_img.src = this.selected_photo.url();
 this.selected_photo_info.innerHTML = 
  '' + this.selected_i + '/' + this.num_photos + ': ' +
  this.selected_photo.file_name + ' ';
 for (var s of this.selected_photo.species) {
  this.selected_photo_info.appendChild(s.button);
 } 
 this.selected_photo_info.appendChild(document.createTextNode(' Ignore:'));
 this.selected_photo_info.appendChild(this.selected_photo.ignore_box);
}

zoo.classifier.select_next = function() {
 if (this.selected_i < this.num_photos - 1) {
  this.select_photo(this.selected_i + 1);
 }
}

zoo.classifier.select_previous = function() {
 if (this.selected_i > 0) {
  this.select_photo(this.selected_i - 1);
 }
}

zoo.classifier.toggle_ignore = function() {
 if (this.selected_photo) {
  this.selected_photo.ignore = ! this.selected_photo.ignore;
  this.selected_photo.ignore_box.checked = this.selected_photo.ignore;
  this.selected_photo.save();
 }
}

zoo.classifier.get_species = function(species_id) {
 var s = null;
 if (species_id in this.species_by_id) {
  s = this.species_by_id[species_id];
 } else {
  s = Object.create(zoo.species);
  s.id = species_id;
  s = s.load();
  this.species_by_id[species_id] = s;
 }
 return s;
} 

zoo.classifier.add_recent_species = function(species_id) {
 var s = this.get_species(species_id);
 if (s == null) {
  return;
 }
 if (! s.button) {
  s.create_dom();
 }

 var rs = this.recent_species;
 while(this.recent_species_div.firstChild) {
  this.recent_species_div.removeChild(this.recent_species_div.firstChild);
 }
 this.recent_species_div.appendChild(s.button);
 this.recent_species = [species_id];

 for (var i of rs) {
  if (i == species_id || this.recent_species.length >= this.max_recent_species) {
   continue;
  }
  this.recent_species.push(i);
  this.recent_species_div.appendChild(this.species_by_id[i].button);
 }
}

zoo.classifier.create_photo_species = function(photo_id,species_id,ps_id) {
 var ps = Object.create(zoo.photo_species);
 ps.photo_id = photo_id;
 ps.species_id = species_id;
 if (ps_id) {
  ps.id = ps_id;
 } else {
  ps.save();
 }
 ps.species = this.species_by_id[ps.species_id];
 ps.button = document.createElement('button');
 ps.button.className = 'species_button';
 ps.button.innerHTML = ps.species.genus + ' ' + ps.species.species;
 return ps;
}

zoo.classifier.add_photo_species = function(species_id) {
 var p = this.selected_photo;
 if (! p) { return; }
 if (species_id in p.species_by_id) {
  return;
 }
 var s = this.get_species(species_id);
 if (s == null) {
  return;
 }
 var ps = this.create_photo_species(p.id, species_id, null);
 p.species.push(ps);
 p.species_by_id[ps.species_id] = ps;
 this.selected_photo_info.appendChild(ps.button);
}