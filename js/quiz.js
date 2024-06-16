var species = {};

species.munch = function(x) {
 var i,n;
 
 this.id = x.id;
 this.order = x.order;
 this.family = x.family;
 this.genus = x.genus;
 this.species = x.species;
 this.common_name = x.common_name;
 this.common_group = x.common_group;
 this.images = x.images;
 this.sounds = x.sounds;
 this.url = x.url; 

 this.next_image = {};
 n = this.images.length;
 for (i = 0; i < n-1; i++) {
  this.next_image[this.images[i]] = this.images[i+1];
 }
 if (n) {
  this.next_image[this.images[n-1]] = this.images[0];
 }
  
 this.state = '';
 this.standard_name = quiz.standardise(this.common_name);
 this.linked_name = this.genus + ' ' + this.species;
 if (this.url) {
  this.linked_name =
   '<a target="_blank" href="' +
   this.url + '">' +
   this.linked_name +
   '</a>';
 }
 this.text = 
  this.common_name + '<br/>' +
  this.linked_name + '<br/>(' +
  this.order + ', ' + 
  this.family + ')';
};

species.random_image = function() {
 if (! this.images) {
  return null;
 }

 var n = this.images.length;
 var i = Math.floor(Math.random() * n);
 return this.images[i];
};

species.image_url = function(i) {
 if (image_data && image_data[i]) {
  return image_data[i];
 } else if (i) {
  return '/zoo/send/send_image.php?id=' + i;
 } else {
  return '';
 }
};

species.set_img_src = function(e,image_id) {
 e.image_id = image_id;
 e.src = this.image_url(image_id);
}

species.set_li_img = function() {
 this.set_img_src(this.main_img,this.random_image());
};

species.rotate_image = function(e) {
 var s = e.image_id || '';
 var t = this.next_image[s];
 if (t) {
  this.set_img_src(e,t);
 } else {
  this.set_img_src(e,this.random_image());
 }
};

species.random_sound = function() {
 if (! this.sounds) {
  return null;
 }

 var n = this.sounds.length;
 var i = Math.floor(Math.random() * n);
 return this.sounds[i];
};

species.sound_url = function(i) {
 if (sound_data && sound_data[i]) {
  return sound_data[i];
 } else if (i) {
  return '/zoo/send/send_sound.php?id=' + i;
 } else {
  return '';
 }
};

species.set_sound_src = function(e,sound_id) {
 e.sound_id = sound_id;
 if (e.sound_id) {
  var u = this.sound_url(sound_id);
  e.src = u;
 }
}

species.set_comparison = function(g) {
 var me = this;
 
 this.comparison_span.innerHTML = g.text;
 g.set_img_src(this.comparison_img,g.random_image());
 this.comparison_img.onclick = function() {
  g.rotate_image(me.comparison_img);
 }
 this.comparison_td.style.display = 'table-cell';
};

species.clear_comparison = function() {
 this.comparison_span.innerHTML = '';
 this.comparison_img.src = '';
 this.comparison_td.style.display = 'none';
};

species.create_dom = function() {
 var li,tb,tr,td,img,me;
 
 li = document.createElement('li');
 this.li = li;
 li.appendChild(tb = document.createElement('table'));
 tb.className = 'quiz_feedback';
 tb.appendChild(tr = document.createElement('tr'));
 tr.appendChild(td = document.createElement('td'));
 this.main_td = td;
 td.appendChild(img = document.createElement('img'));
 this.main_img = img;
 img.className = 'main_image';
 td.appendChild(document.createElement('br'));
 td.appendChild(sp = document.createElement('span'));
 sp.innerHTML = this.text;

 tr.appendChild(td = document.createElement('td'));
 this.comparison_td = td;
 td.style.display = 'none';
 td.appendChild(img = document.createElement('img'));
 this.comparison_img = img;
 img.className = 'comparison_image';
 td.appendChild(document.createElement('br'));
 td.appendChild(sp = document.createElement('span'));
 this.comparison_span = sp; 
 me = this;
 this.main_img.onclick = function() {
  me.rotate_image(me.main_img);
 }
 return(li);
};

//////////////////////////////////////////////////////////////////////

var quiz = {};

quiz.selected_species = null;
quiz.state = 'unanswered';

quiz.good_msg = '<span style="color: green">Correct</span>';
quiz.ok_msg   = '<span style="color: orange">Partly correct</span>';
quiz.bad_msg  = '<span style="color: red">Incorrect</span>';

quiz.num_good  = 0;
quiz.num_ok    = 0;
quiz.num_bad   = 0;
quiz.total_num = 0;

quiz.percent_good = 0;
quiz.percent_ok   = 0;
quiz.percent_bad  = 0;

quiz.species_by_name = {};

quiz.init = function() {
 var n,i,f,ids,id;

 this.is_mobile = 
  /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
 
 this.names = names;
 this.mode = mode;

 this.all_species = [];
 n = all_species.length;

 for (i in all_species) {
  if (! all_species[i]) { continue; }
  f = Object.create(species);
  f.munch(all_species[i]);
  f.create_dom();
  this.all_species.push(f);
  this.species_by_name[f.standard_name] = f;
  this.species_by_name[f.genus.toLowerCase() + f.species] = f;
  this.species_by_name[f.genus.toLowerCase() + ' ' + f.species] = f;
 }

 var ids = [
  'tabber_div',
  'num_good_td',
  'num_ok_td',
  'num_bad_td',
  'total_num_td',
  'percent_good_td',
  'percent_ok_td',
  'percent_bad_td',
  'total_percent_td',
  'questions_div',
  'questions_h2',
  'ok_div',
  'ok_h2',
  'ok_ul',
  'incorrect_div',
  'incorrect_h2',
  'incorrect_ul',
  'answer_box',
  'options_box',
  'mark_box',
  'species_picture_div',
  'species_picture',
  'species_sound'
 ];

 for (i in ids) {
  id = ids[i];
  this[id] = document.getElementById(id);
 }

 var w = Math.max(window.innerWidth, document.documentElement.clientWidth);

 if (this.is_mobile) {
  if (w > 1000) { w = 1000; }
 } else {
  if (w > 500) { w = 500; }
 }
 
 this.tabber_div.style.width = '' + w + 'px';
 var w0 = w - 30;
 
 this.questions_div.style.width = '' + w0 + 'px';
 this.ok_div.style.width        = '' + w0 + 'px';
 this.incorrect_div.style.width = '' + w0 + 'px';

 var w1 = w0 - 30;
 var h1 = Math.round(0.75 * w1);
 this.species_picture_div.style.width  = '' + w1 + 'px';
 this.species_picture_div.style.height = '' + h1 + 'px';

 var w2 = w1 - 30;
 var h2 = Math.round(0.75 * w2);
 this.species_picture.style.width  = '' + w2 + 'px';
 this.species_picture.style.height = '' + h2 + 'px';
 
 this.questions_h2.style['font-size'] = '20pt';
 this.ok_h2.style['font-size'] = '20pt';
 this.incorrect_h2.style['font-size'] = '20pt';

 this.show_question();
};

quiz.show_question = function() {
 var m,j,k,s;
 
 var species_used = {};
 
 this.answer_box.value = '';
 this.mark_box.innerHTML = '';
 this.mark_box.style.display = 'none';
 
 this.options = [];
 m = this.all_species.length;
 if (m > 6) { m = 6;}
 for (i = 0; i < m; i++) {
  j = Math.floor(Math.random() * this.all_species.length);
  while (species_used[j]) {
   j = Math.floor(Math.random() * this.all_species.length);
  }
  species_used[j] = 1;
  this.options.push(this.all_species[j]);
 }
 j = Math.floor(Math.random() * m);
 s = this.options[j];
 
 this.selected_id = j;
 this.selected_species = s;
 this.species_sound.pause();
 this.species_sound.muted = true;
 if (this.mode == 'images') {
  this.selected_species.set_img_src(this.species_picture,this.selected_species.random_image());
  this.species_sound.autoplay = false;
  this.species_sound.muted = true;
  this.selected_species.set_sound_src(this.species_sound,this.selected_species.random_sound());
  if (this.species_sound.sound_id) {
   this.species_sound.load();
  }
 } else {
  this.species_picture.style.display = 'none';
  this.selected_species.set_img_src(this.species_picture,this.selected_species.random_image());
  this.species_sound.autoplay = true;
  this.species_sound.muted = false;
  this.selected_species.set_sound_src(this.species_sound,this.selected_species.random_sound());
  if (this.species_sound.sound_id) {
   this.species_sound.muted = false;
   this.species_sound.load();
   this.species_sound.play();
  }
 } 
 if (! this.is_mobile) {
  this.answer_box.focus();
 }
 this.state = 'unanswered';

 this.options_table = document.createElement('table');
 this.options_table.className = 'options_table';

 for (j = 0; j < m / 2; j++) {
  tr = document.createElement('tr');
  this.options_table.appendChild(tr);
  for (k = 0; k < 2; k++) {
   l = 2 * j + k;
   if (l < m) {
    s = this.options[l];
    td = document.createElement('td');
    if (this.names == 'scientific' || ! s.common_name) {
     td.innerHTML = s.genus + '<br/>' + s.species;
     td.answer = s.genus + ' ' + s.species;
    } else {
     td.innerHTML = s.common_name;
     td.answer = s.common_name;
    }
    this.set_option_handler(td);
    tr.appendChild(td);
   }
  }
 }
 
 this.options_box.style.display = 'none';
 this.options_box.innerHTML = '';
 this.options_box.appendChild(this.options_table);
};

quiz.set_option_handler = function(td) {
 var me = this;
 
 td.onclick = function() { me.use_option(td.answer); };
}

quiz.use_option = function(s) {
 this.answer_box.value = s;
 this.mark_answer();
};

quiz.mark_answer = function() {
 var c,s,f;

 f = this.selected_species;

 var ans =  this.standardise(this.answer_box.value);

 var ans_species = this.species_by_name[ans] || null;

 var img_shown = this.species_picture.src;
 
 var ans1 = this.standardise(f.common_name);
 var ans2 = this.standardise(
             f.genus + 
             f.species
	    );
 var ans3 = this.standardise(f.common_group);
 var ans4 = this.standardise(f.genus);

 if ((ans1 != '' && ans == ans1) || ans == ans2) {
  s = this.good_msg;
  this.num_good++;
  if (f.state == 'ok') {
   f.clear_comparison();
   this.ok_ul.removeChild(f.li);
  } else if (f.state == 'incorrect') {
   f.clear_comparison();
   this.incorrect_ul.removeChild(f.li);
  }
  f.state = '';
 } else if ((ans3 != '' && this.ends_with(ans,ans3)) || 
	    this.starts_with(ans,f.genus.toLowerCase()) ||
	    ans == ans4) {
  s = this.ok_msg;
  this.num_ok++;
  f.main_img.src = img_shown;
  if (ans_species) {
   f.set_comparison(ans_species);
  }
  if (f.state == '') {
   this.ok_ul.appendChild(f.li);
  } else if (f.state == 'incorrect') {
   this.incorrect_ul.removeChild(f.li);
   this.ok_ul.appendChild(f.li);
  }
  f.state = 'ok';
 } else {
  s = this.bad_msg;
  this.num_bad++;
  f.main_img.src = img_shown;
  if (ans_species) {
   f.set_comparison(ans_species);
  }
  if (f.state == '') {
   this.incorrect_ul.appendChild(f.li);
  } else if (f.state == 'ok') {
   this.ok_ul.removeChild(f.li);
   this.incorrect_ul.appendChild(f.li);
  }
  f.state = 'incorrect';
 }
 this.mark_box.innerHTML = 
  s + '<br/>' + 
  f.linked_name + ' (' + 
  f.common_name + ')<br/>(' + 
  f.order + ', ' + f.family + ')';
 this.mark_box.style.display = 'block';

 this.num_good_td.innerHTML = this.num_good;
 this.num_ok_td.innerHTML   = this.num_ok;
 this.num_bad_td.innerHTML  = this.num_bad;

 this.total_num = this.num_good + this.num_ok + this.num_bad;
 if (this.total_num > 0) {
  this.total_num_td.innerHTML = this.total_num;
  this.total_percent_td.innerHTML = 100;
  this.percent_good = Math.round((100. * this.num_good)/this.total_num);
  this.percent_ok   = Math.round((100. * this.num_ok  )/this.total_num);
  this.percent_bad  = Math.round((100. * this.num_bad )/this.total_num);
  this.percent_good_td.innerHTML = this.percent_good;
  this.percent_ok_td.innerHTML   = this.percent_ok;
  this.percent_bad_td.innerHTML  = this.percent_bad;
 }

 if (this.mode == 'images') {
  if (this.species_sound.sound_id) {
   this.species_sound.muted = false;
   this.species_sound.load();
   this.species_sound.play();
  }
 } else {
  this.species_picture.style.display = 'block';
 }

 this.state = 'answered';
};

quiz.handle_keypress = function(e) {
 var keynum;

 if(window.event) {
  keynum = e.keyCode;
 } else if(e.which) {
  keynum = e.which;
 }

 if (keynum == 13) {
  if (this.state == 'unanswered') {
   this.mark_answer();
  } else {
   this.show_question();
  }
  return(0);
 } else {
  return(1);
 }
};

quiz.toggle_options = function() {
 if (this.options_box.style.display == 'none') { 
  this.options_box.style.display = 'block';
 } else {
  this.options_box.style.display = 'none';
 }
};

quiz.standardise = function(s) {
 var t;
 t = s.toLowerCase();
 t = t.replace(/-/g,'');
 t = t.replace(/ /g,'');
 t = t.replace(/\'/g,'');
 return(t);
}

quiz.ends_with = function(s,t) {
 if(s.length >= t.length &&
    s.substring(s.length - t.length,s.length) == t) {
  return(1);
 } else {
  return(0);
 }
}

quiz.starts_with = function(s,t) {
 if(s.length >= t.length &&
    s.substring(0,t.length) == t) {
  return(1);
 } else {
  return(0);
 }
}

