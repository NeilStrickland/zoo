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
zoo.classifier.location = {};

zoo.classifier.add_location = function(name) {
 var o = Object.create(zoo.classifier.location);
 o.name = name;
 o.button = document.createElement('button');
 o.button.className = 'location_button';
 o.button.innerHTML = name;
 o.button.addEventListener('click', function() {
  zoo.classifier.selected_photo_location.value = name;
  zoo.classifier.update_photo();
 }, false);
 this.locations.push(o);
 this.locations_by_name[name] = o;
 this.locations_span.appendChild(o.button);
 return o;
}

zoo.classifier.init = function() {
 this.selected_photo_div         = document.getElementById('selected_photo_div');
 this.selected_photo_img         = document.getElementById('selected_photo_img');
 this.selected_photo_number      = document.getElementById('selected_photo_number');
 this.selected_photo_camera      = document.getElementById('selected_photo_camera');
 this.selected_photo_file_name   = document.getElementById('selected_photo_file_name');
 this.selected_photo_dir         = document.getElementById('selected_photo_dir');
 this.selected_photo_date        = document.getElementById('selected_photo_date');
 this.selected_photo_description = document.getElementById('selected_photo_description');
 this.selected_photo_location    = document.getElementById('selected_photo_location');
 this.locations_span             = document.getElementById('locations_span');
 this.selected_photo_map_link    = document.getElementById('selected_photo_map_link');
 this.selected_photo_ignore      = document.getElementById('selected_photo_ignore');
 this.selected_photo_species     = document.getElementById('selected_photo_species');
 this.recent_species_div         = document.getElementById('recent_species_div');
 this.species_selector           = document.getElementById('species_selector');

 this.selected_photo_description.addEventListener('change', function() {
  zoo.classifier.update_photo();
 }, false);

 this.selected_photo_location.addEventListener('change', function() {
  zoo.classifier.update_photo();
 }, false);

 this.selected_photo_ignore.addEventListener('change', function() {
  zoo.classifier.update_photo();
 }, false);

 this.max_recent_species = 10;

 this.photos = [];
 this.species_by_id = {};
 this.recent_species = [];
 this.locations = [];
 this.locations_by_name = {};

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
  p.camera = p0[1];
  p.dir = p0[2];
  p.file_name = p0[3];
  p.date = p0[4];
  p.location = p0[5];
  p.lat = p0[6];
  p.lng = p0[7];
  p.description = p0[8];
  p.ignore = p0[9];
  p.species = [];
  p.species_by_id = {};
  for (var i = 10; i < p0.length; i++) {
   ps = this.create_photo_species(p.id, p0[i][1], p0[i][0]);
   p.species.push(ps);
   p.species_by_id[ps.species_id] = ps;
  }
  this.photos.push(p);
 } 
 this.num_photos = this.photos.length;

 for (var l of locations) {
  this.add_location(l);
 }

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
 var p = this.photos[i];
 this.selected_photo = p;
 this.selected_photo_img.src = p.url();
 this.selected_photo_number.innerHTML = '' + this.selected_i + '/' + this.num_photos;
 this.selected_photo_camera.innerHTML = p.camera;
 this.selected_photo_file_name.innerHTML = p.file_name;
 this.selected_photo_dir.innerHTML = p.dir;
 this.selected_photo_date.innerHTML = p.date;
 this.selected_photo_description.value = this.selected_photo.description;
 this.selected_photo_location.value = this.selected_photo.location;
 if (p.lat && p.lng) {
  this.selected_photo_map_link.href = 'https://www.google.com/maps/place/' + p.lat + ',' + p.lng;
  this.selected_photo_map_link.style.display = 'inline';
 } else {
  this.selected_photo_map_link.style.display = 'none';
 }
 this.selected_photo_ignore.checked = p.ignore;
 this.selected_photo_species.innerHTML = '';
 for (var s of this.selected_photo.species) {
  this.selected_photo_species.appendChild(s.button);
 } 
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

zoo.classifier.update_photo = function() {
 if (this.selected_photo) {
  this.selected_photo.description = this.selected_photo_description.value;
  this.selected_photo.location = this.selected_photo_location.value;
  this.selected_photo.ignore = this.selected_photo_ignore.checked;
  this.selected_photo.save();

  var l = this.selected_photo_location.value;
  if (l && ! (l in this.locations_by_name)) {
   this.add_location(l);
  }
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
 ps.button.addEventListener('click', function() {
  zoo.classifier.remove_photo_species(ps);
 }, false);
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
 this.selected_photo_species.appendChild(ps.button);
}

zoo.classifier.remove_photo_species = function(ps) {
 var p = this.selected_photo;
 if (! p) { return; }
 if (ps.species_id in p.species_by_id) {
  delete p.species_by_id[ps.species_id];
  for (var i = 0; i < p.species.length; i++) {
   if (p.species[i].species_id == ps.species_id) {
    p.species.splice(i, 1);
    break;
   }
  }
  this.selected_photo_species.removeChild(ps.button);
  ps.delete();
 }
}