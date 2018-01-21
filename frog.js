if (typeof Object.create !== 'function') {
 Object.create = function(o) {
  var F = function() {};
  F.prototype = o;
  return new F();
 };
}

var frog = new Object();

//////////////////////////////////////////////////////////////////////

frog.create_xhr = function() {
 var x;

 x = null;

 if (window.ActiveXObject) {
  x = new ActiveXObject("Microsoft.XMLHTTP");
 } else if(window.XMLHttpRequest) {
  x = new XMLHttpRequest();
 }

 if (x) {
  return(x);
 } else {
  alert('Could not create XMLHttpRequest object to connect to server');
  return(null);
 }
};

//////////////////////////////////////////////////////////////////////

frog.object = new Object();

frog.object.munch = function(x) {
 var i;
 for (i in x) {
  this[i] = x[i];
 }
};

frog.object.scrunch = function(x) {
 var y = Object.create(this);
 y.munch(x);
 return(y);
};

//////////////////////////////////////////////////////////////////////
// Ajax functions

frog.object.load = function() {
 var i,x;

 if (! (this.object_type && this.key && this[this.key])) {
  return(null);
 }

 s =
   'command=load' + 
   '&object_type='  + encodeURIComponent(this.object_type) +
   '&' + this.key + '=' + encodeURIComponent(this[this.key]);

 xhr = frog.create_xhr();

 try {
  xhr.open('GET',this.ajax_url + '/db.php?' + s,false);
 } catch(e) {
  alert('frog.object.load: Could not open XMLHttpRequest object to connect to server');
  return(null);
 }

 try {
  xhr.send(null);
 } catch(e) {
  alert('frog.object.load: Could not send XMLHttpRequest object to connect to server');
  return(null);
 }

 if (xhr.status == 200) {
  x = JSON.parse(xhr.responseText);
  if (x && ! x.error) {
   this.munch(x);
   return(this);
  } else {
   return(null);
  }
 } else {
  return(null);
 }
};

//////////////////////////////////////////////////

frog.object.insert = function() {
 var data,s,xhr;
 data = new Object();

 if (! (this.object_type && this.fields)) {
  return(null);
 }

 for (i in this.fields) {
  if (this.hasOwnProperty(i)) {
   data[i] = this[i];
  }
 }

 data = JSON.stringify(data);

 s =
   'command=insert' + 
   '&object_type='  + encodeURIComponent(this.object_type) +
   '&data='         + encodeURIComponent(data);

 if (this.audit) {
  s += '&audit=1';
 }

 xhr = frog.create_xhr();

 try {
  xhr.open('GET',this.ajax_url + '/db.php?' + s,false);
 } catch(e) {
  alert('frog.object.insert: Could not open XMLHttpRequest object to connect to server');
  return(null);
 }

 try {
  xhr.send(null);
 } catch(e) {
  alert('frog.object.insert: Could not send XMLHttpRequest object to connect to server');
  return(null);
 }

 if (xhr.status == 200) {
  this.id = parseInt(xhr.responseText);
  return(this);
 } else {
  return(null);
 }
};

//////////////////////////////////////////////////

frog.object.update = function() {
 var data;
 data = new Object();

 if (! (this.object_type && this.key && this[this.key] && this.fields)) {
  return(null);
 }

 for (i in this.fields) {
  if (this.hasOwnProperty(i)) {
   data[i] = this[i];
  }
 }

 data = JSON.stringify(data);

 s =
   'command=update' + 
   '&object_type='  + encodeURIComponent(this.object_type) +
   '&' + this.key + '=' + encodeURIComponent(this[this.key]) + 
   '&data='         + encodeURIComponent(data);

 if (this.audit) {
  s += '&audit=1';
 }

 xhr = frog.create_xhr();

 try {
  xhr.open('GET',this.ajax_url + '/db.php?' + s,false);
 } catch(e) {
  alert('frog.object.update: Could not open XMLHttpRequest object to connect to server');
  return(null);
 }

 try {
  xhr.send(null);
 } catch(e) {
  alert('frog.object.insert: Could not send XMLHttpRequest object to connect to server');
  return(null);
 }

 if (xhr.status == 200) {
  return(this);
 } else {
  return(null);
 }
};

//////////////////////////////////////////////////

frog.object.save = function() {
 if (this.key && this[this.key]) {
  this.update();
 } else {
  this.insert();
 }
};

//////////////////////////////////////////////////

// NB we cannot call this method 'delete' because that is a reserved
// word in Javascript.
frog.object.del = function() {
 if (! (this.object_type && this.key && this[this.key])) {
  return(null);
 }

 s =
   'command=delete' + 
   '&object_type='  + encodeURIComponent(this.object_type) +
   '&' + this.key + '=' + encodeURIComponent(this[this.key]);

 if (this.audit) {
  s += '&audit=1';
 }

 xhr = frog.create_xhr();

 try {
  xhr.open('GET',this.ajax_url + '/db.php?' + s,false);
 } catch(e) {
  alert('frog.object.del: Could not open XMLHttpRequest object to connect to server');
  return(null);
 }

 try {
  xhr.send(null);
 } catch(e) {
  alert('frog.object.del: Could not send XMLHttpRequest object to connect to server');
  return(null);
 }

 if (xhr.status == 200) {
  return(this);
 } else {
  return(null);
 }
};

//////////////////////////////////////////////////////////////////////


frog.do_command = function(c) {
 var f = document.main_form;
 var i,e;

 if (c == 'save') {
  if (! window.saving) {
   frog.wait_then_save(10);
  }
 } else {
  f.command.value = c;
  f.submit();
 }
};

frog.do_control_command = function(c) {
 var f = document.control_form;
 f.command.value = c;
 f.submit();
};

frog.save_value = function(id) {
 frog.saved_values[id] = document.getElementById(id).value;
 return(true);
};

frog.set_form_changed = function() {
 if (document.main_form) {
  document.main_form.changed = 1;
 }
};

frog.wait_then_save = function(n) {
 var f = document.main_form;
 var i,e,done,m;

 window.saving = 1;
 done = 1;
 
 if (n > 0) {
  for (i in f.elements) {
   e = f.elements[i];
   if (e && e.converting) {
    done = 0;
   }
  }
 }

 if (done) {
  //console.log('finished converting, now save');
  f.command.value = 'save';
  f.submit();
  //  alert('done');
 } else {
  // LaTeX to HTML conversion still running
  m = n-1;
  window.status = 'converting latex (' + m + ')';
  //console.log('converting latex (' + m + ')');
  setTimeout(function() { frog.wait_then_save(m); },500);
 }
};

//////////////////////////////////////////////////////////////////////
// Date functions

frog.format_date = function(x) {
 var w,y,m,d;
 if (x instanceof Date) {
  w = x;
 } else {
  w = new Date();
  if (x) {
   w.setTime(x);
  } 
 }

 y = w.getFullYear();
 m = 1 + w.getMonth();
 m = '' + m;
 if (m.length == 1) { m = '0' + m; }
 d = w.getDate();
 d = '' + d;
 if (d.length == 1) { d = '0' + d; }

 return(y + '-' + m + '-' + d);
};

frog.dmy = function(d) {
 if (d.length < 10) {
  return('');
 } else {
  return(d.substring(8,10) + '/' + d.substring(5,7) + '/' + d.substring(2,4));
 }
};

frog.todays_date = function() {
 var t = new Date();
 var y = t.getFullYear();
 var m = t.getMonth() + 1;
 var d = t.getDate();
 if (m < 10) { m = '0' + m; }
 if (d < 10) { d = '0' + d; }
 return('' + y + '-' + m + '-' + d);
};

frog.check_date = function(id) {
 var e = document.getElementById(id);
 var d = e.value;
 var n,m,day,month,year;

 if (d == '=') {
  d = frog.todays_date();
  e.value = d;
  this.saved_values[id] = d;
 }

 if ((n = d.indexOf('/')) >= 0) {
  day = d.substring(0,n);
  m = d.indexOf('/',n+1);
  if (m >= 0) {
   month = d.substring(n+1,m);
   year = d.substring(m+1);
  } else {
   month = d.substring(n+1);
   year = (new Date()).getFullYear();
  }

  while(   day.length < 2 ) {   day = '0' +   day; }
  while( month.length < 2 ) { month = '0' + month; }

  if (year.length < 4) {
   if (year >= 80) {
    year = '19' + year;
   } else {
    year = '20' + year;
   }
  }

  d = year + '-' + month + '-' + day;
  e.value = d;
  this.saved_values[id] = d;
 }

 if (1) {
  this.saved_values[id] = d;
 } else {
  alert('Date ' + d + ' is invalid.  Please enter dates in the form YYYY-MM-DD.');
  e.value = this.saved_values[id];
 }
};

//////////////////////////////////////////////////////////////////////
// Form element functions

frog.force_checkbox = function() {
 if (this.name && this.name.length > 3 && this.name.substr(-3) == '_cb') {
  n = this.name.substr(this.name.length - 3);
  p = document.getElementById(n);
  p.value = this.checked ? 1 : 0;
 }
}

frog.check_url_element = function(id) {
 var e = document.getElementById(id);
 if (e && e.value) { window.open(e.value); }
};

// If s is a Select object then s.value is browser-dependent
// In Firefox, s.value is the value that would be sent if the
// form were submitted.  In IE it is empty.
// If o is an Option object, then o.value is again browser-dependent.
// In IE, o.value defaults to the empty string if no value attribute
// is supplied.  In Firefox, it defaults instead to the text
// content of the option.

// The frog.selected_value function mimics Firefox's s.value.

frog.selected_value = function(s) {
 if (s.selectedIndex == -1) {
  return(null);
 } else {
  var o = s.options[s.selectedIndex];
  if (o.text && (! o.value) && (o.innerHTML != '&nbsp;')) {
   return(o.text);
  } else {
   return(o.value);
  }
 }
};

frog.set_selected_value = function(s,v) {
 var i,w,o,found;

 found = 0;

 for (i in s.options) {
  o = s.options[i];
  w = null;
  if (! o) { continue; }
  w = o.value;
  if (!w && o.text) { w = o.text; }
  if (w && (w == v)) {
   s.selectedIndex = i;
   found = 1;
   break;
  }
 }

 if (! found) { s.selectedIndex = 0; }
};


frog.text_pane_onchange = function(f) {
 var t = document.getElementById(f);
 var d = document.getElementById('div_' + f);
 d.innerHTML = t.value;
};

frog.text_pane_preview = function(f) {
 var t = document.getElementById(f);
 var d = document.getElementById('div_' + f);
 d.innerHTML = t.value;
};

frog.text_pane_select_choice = function(f) {
 var t = document.getElementById(f);
 var s = document.getElementById(f + '_choice');
 var d = document.getElementById('div_' + f);
 t.value = frog.selected_value(s);
 d.innerHTML = t.value;
};

frog.checkbox_hide = function(cb,id) {
 var e,v;

 v = cb.checked ? 'hidden' : 'visible';
 e = document.getElementById(id);
 if (e) { e.style.visibility = v; }
};

frog.checkbox_hide_siblings = function(cb,n) {
 var v,x,p,e,i,j;
 v = cb.checked ? 'hidden' : 'visible';
 x = cb;
 for (i = 0; i < n; i++) { x = x.parentNode; }
 p = x.parentNode;
 for (i in p.childNodes) {
  e = p.childNodes[i];
  if (e != x && e.style) {
   e.style.visibility = v;
  }
 }
};

frog.toggle_display  = function(x,id,t) {
 var tt = t ? t : '';
 var e = document.getElementById(id);
 if (e && e.style) {
  if (e.style.display == 'none') {
   e.style.display = tt;
   x.src = frog.icons_url + '/contract.png';
  } else {
   e.style.display = 'none';
   x.src = frog.icons_url + '/expand.png';
  }
 }
};

//////////////////////////////////////////////////////////////////////

frog.object.fill_from_document = function(prefix) {
 var i,e,f;
 for (i in this.fields) {
  f = this.fields[i];
  e = document.getElementById(prefix + i);
  if (e) {
   if (e.tagName === 'SELECT') {
    this[i] = frog.selected_value(e);
   } else if (e.tagName === 'INPUT' &&  e.type === 'checkbox') {
    this[i] = e.checked ? 1 : 0;
   } else if (e.hasOwnProperty('value')) {
    this[i] = e.value;
   }
  }
 }
};

frog.object.fill_from_form = function(form,prefix) {
 var i,e,f;
 for (i in this.fields) {
  f = this.fields[i];
  e = form.elements[prefix + i];
  if (e) {
   if (e.tagName === 'SELECT') {
    this[i] = frog.selected_value(e);
   } else if (e.tagName === 'INPUT' &&  e.type === 'checkbox') {
    this[i] = e.checked ? 1 : 0;
   } else if (typeof e.value === 'string') {
    this[i] = e.value;
   }
  }
 }
};

//////////////////////////////////////////////////////////////////////

frog.object.watch = function(e) {
 var me = this;

 if (e.tagName === 'INPUT') {
  if (e.getAttribute('type') === 'checkbox') {
   e.onclick = function(ev) { return(me.handle_event(ev)); };
  } else {
   e.onchange = function(ev) { return(me.handle_event(ev)); };
  }
 }
};

frog.munch_all = function(objects,proto) {
 var i,o,oo;

 oo = new Array();

 for (i in objects) {
  o = Object.create(proto);
  o.munch(objects[i]);
  oo[i] = o;
 }

 return(oo);
};

//////////////////////////////////////////////////////////////////////

frog.relative_offset = function(p,q) {
 var dx = 0;
 var dy = 0;
 var r = q;
 var chain = r.id + '(' + r.tagName + ')';
 while (r && (r != p)) {
  dx += r.offsetLeft;
  dy += r.offsetTop;
  r = r.offsetParent;
  if (r) {
   chain = chain + '>' + r.id + '(' + r.tagName + ')';
  } else {
   chain = chain + '>null';
  }
 }
 if (r) {
  var xy = new Object();
  xy.left = dx;
  xy.top = dy;
  return(xy);
 } else {
  alert('Could not find relative offset (' + chain + ')');
  return(null);
 }
};

frog.total_offset = function(q) {
 var dx = 0;
 var dy = 0;
 var r = q;
 var chain = r.id;
 while (r) {
  dx += r.offsetLeft;
  dy += r.offsetTop;
  r = r.offsetParent;
 }
 var xy = new Object();
 xy.left = dx;
 xy.top = dy;
 return(xy);
};

//////////////////////////////////////////////////////////////////////


frog.trim = function(s) {
 while (s.substring(0,1) == ' ') {
  s = s.substring(1,s.length);
 }
 while (s.substring(s.length-1,s.length) == ' ') {
  s = s.substring(0,s.length-1);
 }
 return s;
};

