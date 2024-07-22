autosuggest = new Object();

autosuggest.onUseSuggestion = null;

autosuggest.create = function(type,parent,key,value) {
 var hidden,display,size;
 hidden = document.createElement('input');
 hidden.setAttribute('type','hidden');
 hidden.value = key;

 display = document.createElement('input');
 display.setAttribute('type','text');
 display.className = 'autosuggest ' + type;

 size = this.default_size[type];
 if (size) { display.setAttribute('size',size); }

 display.value = value;
 
 parent.appendChild(hidden);
 parent.appendChild(display);

 return(autosuggest.setup(type,hidden,display,key,value));
};

autosuggest.setup = function(type,hidden,display,key,value) {
 if (this.known_types[type]) {
  url = 'ajax/suggest.php?type=' + type;
  hidden.value = key;
  display.value = value;
  size = this.default_size[type];
  if (size) { display.setAttribute('size',size); }
  return(this.setup_ajax(hidden,display,url));
 } else if (window[type + '_keys'] && window[type_ + 'vals']) {
  keys = window[type + '_keys'];
  vals = window[type + '_vals'];
  return(this.setup_list(hidden,display,keys,vals,key));
 } else {
  return(this.setup_list(hidden,display,[],[],key));
 }
};

autosuggest.setup_list = function(hidden,display,keys,vals,key) {
 var a = Object.create(autosuggest);
 a.ajax_url = null;
 a.keys = keys;
 a.vals = vals;
 a.fade = new Object();
 a.key = key;
 a.setup_general(hidden,display);
 return(a);
};

autosuggest.setup_ajax = function(hidden,display,url) {
 var a = Object.create(autosuggest);
 a.ajax_url = url;
 a.keys = new Array();
 a.vals = new Object();
 a.fade = new Object();
 a.setup_general(hidden,display);
 return(a);
};

autosuggest.setup_general = function(hidden,display) {
 var me = this;

 this.elem = display;
 this.key_elem = hidden;

 this.maxSuggestions = 3;

 //A reference to the element we're binding the list to.
 this.elem.peer = this;
 this.key_elem.peer = this;
 
 if (this.key && ! this.ajax_url) {
  this.key_elem.value = this.key;
  this.elem.value = this.vals[this.key];
 }

 //Arrow to store a subset of eligible suggestions that match the user's input
 this.eligible = new Array();

 //The text input by the user.
 this.inputText = null;

 //A pointer to the index of the highlighted eligible item. -1 means nothing highlighted.
 this.highlighted = -1;

 this.visibleStart = 0;
 this.visibleEnd = 0;
 
 this.error_div = this.get_error_div();

 //Do you want to remember what keycode means what? Me neither.
 var TAB = 9;
 var RET = 13;
 var ESC = 27;
 var SHIFT = 16;
 var KEYUP = 38;
 var KEYDN = 40;
	
 //The browsers' own autocomplete feature can be problematic, since it will 
 //be making suggestions from the users' past input.
 //Setting this attribute should turn it off.
 this.elem.setAttribute("autocomplete","off");

 /********************************************************
	onkeydown event handler for the input elem.
	Tab key = use the highlighted suggestion, if there is one.
	Esc key = get rid of the autosuggest dropdown
	Up/down arrows = Move the highlight up and down in the suggestions.
 ********************************************************/
 this.elem.onkeydown = function(ev) {
  var key = me.getKeyCode(ev);

  switch(key) {
   case TAB:
    me.useSuggestion(true);
    break;

   case RET:
    me.useSuggestion(false);
    break;

   case ESC:
    me.hideDiv();
    break;

   case KEYUP:
    if (me.highlighted > 0)
     {
      me.highlighted--;
     }
    me.changeHighlight(key);
    break;

   case KEYDN:
    if (me.highlighted < (me.eligible.length - 1))
     {
      me.highlighted++;
     }
    me.changeHighlight(key);
    break;
   }

 };

 /********************************************************
	onkeyup handler for the elem
	If the text is of sufficient length, and has been changed, 
	then display a list of eligible suggestions.
 ********************************************************/
 this.elem.onkeyup = function(ev) {
  var key = me.getKeyCode(ev);

  switch(key) {
    //The control keys were already handled by onkeydown, so do nothing.
   case TAB:
   case RET:
   case ESC:
   case SHIFT:
   case KEYUP:
   case KEYDN:
    return;
   default:

    if (this.value != me.inputText && this.value.length > 0) {
      me.inputText = this.value;
      me.getEligible();
    } else {
     if (this.value.length == 0) {
      me.hideDiv();
     }
    }
   }
 };

 this.elem.onblur = function() {
  me.hideDiv();
 }

};

autosuggest.checkConsistency = function() {
 var e = this.elem;
 var v = e.value;
 var k = this.key_elem;
 var i = k.value;

 if (v) {
  if (this.eligible_set && ! this.eligible.length) {
   this.error_div.style.display = 'block';
   k.value = '';
  }
 } else {
  k.value = '';
  if (i && k.onchange) {
   k.onchange();
  }
 }
};

/********************************************************
	Insert the highlighted suggestion into the input box, and 
	remove the suggestion dropdown.
********************************************************/

autosuggest.useSuggestion = function(is_tab) {
 var e = this.elem;
 var v = e.value;
 var k = this.key_elem;
 var i = k.value;

 if (this.highlighted == -1 && this.eligible.length == 1) {
  this.highlighted = 0;
 }
 if (this.highlighted > -1) {
  k.value  = this.eligible[this.highlighted];
  e.value = this.vals[k.value];
  if (k.value != i && k.onchange) {
   k.onchange();
  }

  this.hideDiv();
  if (is_tab) {
   //It's impossible to cancel the Tab key's default behavior. 
   //So this undoes it by moving the focus back to our field right after
   //the event completes.
   //   setTimeout(function() { e.focus(); },0);
  }
 }

 if (this.onUseSuggestion) {
  this.onUseSuggestion();
 }
};

/********************************************************
	Display the dropdown. Pretty straightforward.
********************************************************/
autosuggest.showDiv = function() {
 this.error_div.style.display = 'none';
 if (! this.div) { this.createEmptyDiv(); }
 this.div.style.display = 'block';
};

autosuggest.createEmptyDiv = function () {
 var d = document.getElementById('autosuggest_div');
 if (d) { 
  this.div = d;
 } else {
  d = document.createElement('div');
  d.setAttribute('id','autosuggest_div');
  d.style.display = 'none';
  document.body.appendChild(d);
  this.div = d;
 }
};

/********************************************************
	Hide the dropdown and clear any highlight.
********************************************************/
autosuggest.hideDiv = function() {
 if (! this.div) { this.createEmptyDiv(); }
 this.div.style.display = 'none';
 this.highlighted = -1;
 this.checkConsistency();
};

/********************************************************
	Modify the HTML in the dropdown to move the highlight.
********************************************************/
autosuggest.changeHighlight = function() {
 if (! this.div) { this.createEmptyDiv(); }
 var lis = this.div.getElementsByTagName('LI');
 for (i in lis) {
  var li = lis[i];
  
  if (this.highlighted == i) {
   li.className = "selected";
  } else {
   if (li) { li.className = ""; }
  }
 }
};

/********************************************************
	Position the dropdown div below the input text field.
********************************************************/
autosuggest.positionDiv = function() {
 if (! this.div) { this.createEmptyDiv(); }

 var el = this.elem;
 var x = 0;
 var y = el.offsetHeight;
	
 //Walk up the DOM and add up all of the offset positions.
 while (el.offsetParent && el.tagName.toUpperCase() != 'BODY') {
  x += el.offsetLeft;
  y += el.offsetTop;
  el = el.offsetParent;
 }

 x += el.offsetLeft;
 y += el.offsetTop;

 this.div.style.left = x + 'px';
 this.div.style.top = y + 'px';
 this.error_div.style.left = x + 'px';
 this.error_div.style.top = y + 'px';
};

/********************************************************
	Build the HTML for the dropdown div
********************************************************/
autosuggest.createDiv = function() {
 var me = this;
 var ul = document.createElement('ul');
 if (! this.div) {this.createEmptyDiv(); } 
 
 this.visibleStart = 0;
 this.visibleEnd = Math.min(this.maxSuggestions,this.eligible.length);
 if (this.highlighted >= this.visibleEnd) {
  this.visibleEnd = this.highlighted-1;
  this.visibleStart = this.visibleEnd - this.maxSuggestions;
  if (this.visibleStart < 0) {
   this.visibleStart = 0;
  }
 }

 //Create an array of LI's for the words.
 for (i in this.eligible) {
  var word = this.vals[this.eligible[i]];
  if (this.fade && this.fade[this.eligible[i]]) {
   word = '<span class="fade">' + word + '</span>';
  }
	
  var li = document.createElement('li');
  var a = document.createElement('a');
  a.href="javascript:false";
  a.innerHTML = word;
  li.appendChild(a);
	
  if (this.highlighted == i) {
   li.className = "selected";
  } else if (i < this.visibleStart ||
	     i >= this.visibleEnd) {
   li.className = "outOfRange";
  }

  ul.appendChild(li);
 }
	
 if (this.div.childNodes.length == 0) {
  this.div.appendChild(ul);
 } else {
  this.div.replaceChild(ul,this.div.childNodes[0]);
 }

 /********************************************************
		mouseover handler for the dropdown ul
		move the highlighted suggestion with the mouse
 ********************************************************/
 ul.onmouseover = function(ev) {
  //Walk up from target until you find the LI.
  var target = me.getEventSource(ev);
  while (target.parentNode && target.tagName.toUpperCase() != 'LI') {
   target = target.parentNode;
  }
		
  var lis = me.div.getElementsByTagName('LI');
  for (i in lis)  {
   var li = lis[i];
   if(li == target) {
    me.highlighted = i;
    break;
   }
  }
  me.changeHighlight();
 };

 /********************************************************
		click handler for the dropdown ul
		insert the clicked suggestion into the input
 ********************************************************/
 ul.onmousedown = function(ev) {
  me.useSuggestion(false);
  me.hideDiv();
  me.cancelEvent(ev);
  return false;
 };
	
 this.div.className="suggestion_list";
 this.div.style.position = 'absolute';
 
};

/********************************************************
	determine which of the suggestions matches the input
********************************************************/
autosuggest.getEligible = function() {
 var me = this;
 if (this.ajax_url) {
  if (window.ActiveXObject) {
   x = new ActiveXObject("Microsoft.XMLHTTP");
  } else if(window.XMLHttpRequest) {
   x = new XMLHttpRequest();
  }
   
  x.open("GET",this.ajax_url + '&s=' + escape(this.inputText));
  x.onreadystatechange = function() {
   if (x.readyState == 4) {
    if (x.status == 200) {
     var r = eval(x.responseText);
     me.keys = r.keys;
     me.vals = r.vals;
     me.fade = r.fade ? r.fade : new Object();
     me.eligible = new Array();
     for (i in me.keys) {
      me.eligible[me.eligible.length] = me.keys[i];
     }
     me.createDiv();
     me.positionDiv();
     me.showDiv();
     me.eligible_set = 1;
    }
   }
  };

  x.send(null);
 } else {
  this.eligible = new Array();
  for (i in this.keys) {
   var suggestion = this.vals[this.keys[i]];
   
   if(suggestion.toLowerCase().indexOf(this.inputText.toLowerCase()) == "0") {
    this.eligible[this.eligible.length]= this.keys[i];
   }
  }
  this.createDiv();
  this.positionDiv();
  this.showDiv();
  this.eligible_set = 1;
 }
};

/********************************************************
	Helper function to determine the keycode pressed in a 
	browser-independent manner.
********************************************************/
autosuggest.getKeyCode = function(ev) {
 if(ev) { return ev.keyCode; } //Moz
 if(window.event) { return window.event.keyCode; }
};

/********************************************************
	Helper function to determine the event source element in a 
	browser-independent manner.
********************************************************/
autosuggest.getEventSource = function(ev) {
 if(ev) { return ev.target; }
 if(window.event) { return window.event.srcElement; }
};

/********************************************************
	Helper function to cancel an event in a 
	browser-independent manner.
	(Returning false helps too).
********************************************************/
autosuggest.cancelEvent = function(ev) {
 if(ev) {
  ev.preventDefault();
  ev.stopPropagation();
 }
 if(window.event) {
  window.event.returnValue = false;
 }
};

autosuggest.get_error_div = function() {
 var e;
 var d = document.getElementById('autosuggest_error_div');
 if (d) { 
  return(d);
 } else {
  d = document.createElement('div');
  d.id = 'autosuggest_error_div';
  d.style.display = 'none';
  d.appendChild(document.createTextNode('Unrecognised value - '));
  a = document.createElement('a');
  a.href = 'javascript:autosuggest.show_error_details()';
  a.innerHTML = 'more details';
  d.appendChild(a);
  d.appendChild(document.createTextNode(' - '));
  a = document.createElement('a');
  a.href = 'javascript:autosuggest.hide_error()';
  a.innerHTML = 'close';
  d.appendChild(a);
  e = document.createElement('div');
  e.id = 'autosuggest_error_details_div';
  e.style.display = 'none';
  e.appendChild(document.createElement('br'));
  e.appendChild(document.createTextNode(
   '(Autosuggest error explanation can be entered here '
  ));
  d.appendChild(e);
  return(d);
 }
};

autosuggest.show_error_details = function() {
 var d = document.getElementById('autosuggest_error_details_div');
 if (d) { d.style.display = 'block'; }
};

autosuggest.hide_error = function() {
 var d = document.getElementById('autosuggest_error_details_div');
 if (d) { d.style.display = 'none'; }
 var d = document.getElementById('autosuggest_error_div');
 if (d) {
  d.style.display = 'none';
 }
};

autosuggest.set_value = function(i) {
 var x,i,me;
 this.hideDiv();
 if (i == '' || i == null) {
  this.key_elem.value = '';
  this.elem.value = '';
 } else {
  this.key_elem.value = i;
  if (this.ajax_url) {
   if (window.ActiveXObject) {
    x = new ActiveXObject("Microsoft.XMLHTTP");
   } else if(window.XMLHttpRequest) {
    x = new XMLHttpRequest();
   }
   
   x.open("GET",this.ajax_url + '&id=' + i);
   me = this;
   x.onreadystatechange = function() {
    if (x.readyState == 4) {
     if (x.status == 200) {
      me.elem.value = x.responseText;
     }
    }
   };
   
   x.send(null);
  } else if (this.vals && this.vals[i]) {
   this.elem.value = this.vals[i];
  }
 }
};

autosuggest.known_types = {
    'species' : 1,
    'taxon' : 1,
    'kingdom' : 1,
    'phylum' : 1,
    'class' : 1,
    'order' : 1,
    'family' : 1
};

autosuggest.default_size = {
    'species' : 65,
    'taxon' : 65,
    'kingdom' : 30,
    'phylum' : 30,
    'class' : 30,
    'order' : 30,
    'family' : 30
};

autosuggest.extra_params = {
    'species' : [],
    'taxon' : [],
    'kingdom' : [],
    'phylum' : [],
    'class' : [],
    'order' : [],
    'family' : []
};

autosuggest.setup_all = function() {
    var i,ip,ips,d,s,url,p,v,size;
 ips = document.getElementsByTagName('INPUT');

 for (i in ips) {
  ip = ips[i];
  if (ip.type != 'hidden') { continue; }
  if (! ip.id) { continue; }
  d = document.getElementById(ip.id + '_display');
  if (! d) { continue; }
  if (ip.className && ip.className.length > 11 &&
      ip.className.substr(0,12) == 'autosuggest_') {
   s = ip.className.substr(12);

   if (this.known_types[s]) {
    url = 'ajax/suggest.php?type=' + s;

    if (this.extra_params[s]) {
     for (p of this.extra_params[s]) {
      v = ip.getAttribute(p);
      if (v) {
       if (v.endsWith('_VALUE')) {
        k = v.slice(0,-6);
        v = document.getElementById(k).value;
       }
       url = url + '&' + p + '=' + v;
      }
     }
    }
       
    size = this.default_size[s];
    if (size) { d.setAttribute('size',size); }

    this.setup_ajax(ip,d,url);
   }
  }
 }
};

if (! window.loaded_scripts) {
 window.loaded_scripts = new Object();
}

window.loaded_scripts.autosuggest = 1;
