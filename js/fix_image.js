var fixer = {};

fixer.drag_mode = 'none';
fixer.x = 0;
fixer.y = 0;
fixer.w = 0;
fixer.h = 0;
fixer.t = 0;
fixer.ww = 0;
fixer.hh = 0;
fixer.ar = 1;
fixer.last_x = 0;
fixer.last_y = 0;
fixer.left_bar = null;
fixer.right_bar = null;
fixer.top_bar = null;
fixer.bottom_bar = null;

fixer.init = function(x,y,w,h,x0,y0,w0,h0,ww,hh,ar,next_image,previous_image) {
 var me = this;
 
 this.x = x;
 this.y = y;
 this.w = w;
 this.h = h;
 this.x0 = x0;
 this.y0 = y0;
 this.w0 = w0;
 this.h0 = h0;
 this.ww = ww;
 this.hh = hh;
 this.ar = ar;
 this.next_image = next_image;
 this.previous_image = previous_image;

 this.is_fat = (this.w0 > this.ar * this.h) ? 1 : 0;
 
 this.t = 4;

 this.main_div   = document.getElementById("main_div");
 this.resize_div = document.getElementById("resize_div");
 this.middle_div = document.getElementById("middle_div");
 this.glass_div  = document.getElementById("glass_div");
 this.main_image = document.getElementById("main_image");
 this.main_form  = document.getElementById("main_form");
 
 this.left_bar   = this.create_bar(this.glass_div);
 this.right_bar  = this.create_bar(this.glass_div);
 this.top_bar    = this.create_bar(this.glass_div);
 this.bottom_bar = this.create_bar(this.glass_div);
 
 this.main_div.style.position = 'relative';
 this.main_div.style.top      = '0px';
 this.main_div.style.left     = '0px';
 this.main_div.style['z-index'] = '0';
 this.main_div.style['background-color'] = 'white';
 this.main_div.style.display = 'block';

 this.middle_div.style.position = 'relative';
 this.middle_div.style.top      = '0px';
 this.middle_div.style.left     = '0px';
 this.middle_div.style.width    = '' + this.ww + 'px';
 this.middle_div.style.height   = '' + this.hh + 'px';
 this.middle_div.style['background-color'] = '#DDDDDD';

 this.main_image.style.position = 'absolute';
 this.main_image.style.left     = '' + this.x0 + 'px';
 this.main_image.style.top      = '' + this.y0 + 'px';
 this.main_image.style.width    = '' + this.w0 + 'px';
 this.main_image.style.height   = '' + this.h0 + 'px';
 this.glass_div.style['z-index'] = '1';

 this.glass_div.style.position = 'relative';
 this.glass_div.style.top    = '0px';
 this.glass_div.style.left   = '0px';
 this.glass_div.style.width  = '' + this.ww + 'px';
 this.glass_div.style.height = '' + this.hh + 'px';
 this.glass_div.style['z-index'] = '2';
 this.glass_div.style['background-color'] = 'transparent';

 this.left_bar.style.position   = 'absolute';
 this.right_bar.style.position  = 'absolute';
 this.top_bar.style.position    = 'absolute';
 this.bottom_bar.style.position = 'absolute';

 this.left_bar.style['background-color']   = 'gray';
 this.right_bar.style['background-color']  = 'gray';
 this.top_bar.style['background-color']    = 'gray';
 this.bottom_bar.style['background-color'] = 'gray';

 this.move_bars(0,0);

 var oo = [
  'resize_div','glass_div','main_image',
  'left_bar','right_bar','top_bar','bottom_bar'
 ];

 for (i in oo) {
  this[oo[i]].onmousedown = function(e) { me.mousedown_handler(e) };
 }
 
 document.onmousemove =
  function(e) { me.mousemove_handler(e); };

 document.onmouseup =
  function(e) { me.mouseup_handler(e); };

 document.body.onkeydown =
  function(e) { me.keypress_handler(e); }

 document.body.onwheel =
  function(e) { me.wheel_handler(e); }
};

fixer.create_bar = function(parent) {
 var b = document.createElement('div');
 b.className = 'bar';
 b.style.background = '#444444';
 b.style.position = 'absolute';
 parent.appendChild(b);
 return b;
};

fixer.move_bars = function(dx,dy,mode_) {
 var dx_min,dx_max,dy_min,dy_max,dx0,dy0,mode;

 mode = mode_ || this.drag_mode;
 
 if (mode == 'move') {
  dx_min = this.t - this.x;
  dx_max = this.ww - this.x - this.w - this.t;
  dy_min = this.t - this.y;
  dy_max = this.hh - this.y - this.h - this.t;
  dx0 = Math.min(dx_max,Math.max(dx_min,dx));
  dy0 = Math.min(dy_max,Math.max(dy_min,dy));
  this.x = this.x + dx0;
  this.y = this.y + dy0;
 } else if (mode == 'left') {
  dx_min = Math.max(this.t - this.x,
		    Math.round(this.ar*(this.h+this.t+this.y-this.hh)));
  dx_max = this.w - 12;
  dx0 = Math.min(dx_max,Math.max(dx_min,dx));
  this.x = this.x + dx0;
  this.w = this.w - dx0;
  this.h = Math.round(this.w/this.ar);
 } else if (mode == 'right') {
  dx_min = 12 - this.w;
  dx_max = Math.min(this.ww - this.x - this.w - this.t,
		    Math.round(this.ar*(this.hh-this.h-this.t-this.y)));
  dx0 = Math.min(dx_max,Math.max(dx_min,dx));
  this.w = this.w + dx0;
  this.h = Math.round(this.w/this.ar);
 } else if (mode == 'top') {
  dy_min = Math.max(this.t - this.y,
		    Math.round((this.w+this.t+this.x-this.ww)/this.ar));
  dy_max = this.h - 16;
  dy0 = Math.min(dy_max,Math.max(dy_min,dy));
  this.y = this.y + dy0;
  this.h = this.h - dy0;
  this.w = Math.round(this.h*this.ar);
 } else if (mode == 'bottom') {
  dy_min = 16 - this.h;
  dy_max = Math.min(this.hh - this.y - this.h - this.t,
		    Math.round((this.ww-this.w-this.t-this.x)/this.ar));
  dy0 = Math.min(dy_max,Math.max(dy_min,dy));
  this.h = this.h + dy0;
  this.w = Math.round(this.h*this.ar);
 }

 this.set_bars();
};

fixer.scale = function(step) {
 this.x = Math.max(0, this.x-step);
 this.y = Math.max(0, this.y-step);
 this.w = Math.max(10, this.w+2*step);
 this.h = Math.round(0.75*this.w);
 this.set_bars();
};

fixer.set_bars = function() {
 this.left_bar.style.left     = (this.x-this.t)        + 'px';
 this.left_bar.style.top      = (this.y)               + 'px';
 this.left_bar.style.width    = (this.t)               + 'px';
 this.left_bar.style.height   = (this.h)               + 'px'; 
 this.right_bar.style.left    = (this.x+this.w)        + 'px';
 this.right_bar.style.top     = (this.y)               + 'px';
 this.right_bar.style.width   = (this.t)               + 'px';
 this.right_bar.style.height  = (this.h)               + 'px'; 
 this.top_bar.style.left      = (this.x-this.t)        + 'px';
 this.top_bar.style.top       = (this.y-this.t)        + 'px'; 
 this.top_bar.style.width     = (this.t+this.w+this.t) + 'px';
 this.top_bar.style.height    = (this.t)               + 'px'; 
 this.bottom_bar.style.left   = (this.x-this.t)        + 'px';
 this.bottom_bar.style.top    = (this.y+this.h)        + 'px'; 
 this.bottom_bar.style.width  = (this.t+this.w+this.t) + 'px';
 this.bottom_bar.style.height = (this.t)               + 'px'; 
};

fixer.mousemove_handler = function(e) {
 if (this.drag_mode != 'none') {
  if (!e) {
   var e = window.event;
  }
  var dx = e.clientX - this.last_x;
  var dy = e.clientY - this.last_y;
  this.last_x = e.clientX;
  this.last_y = e.clientY;
  this.move_bars(dx,dy,this.drag_mode);
 }
 return false;
};

fixer.mousedown_handler = function(e) {
 if (!e) {
  var e = window.event;
 }
 if (e.target) {
  targ = e.target;
 } else if (e.srcElement) {
  targ = e.srcElement;
 }

 this.last_x = e.clientX;
 this.last_y = e.clientY;

 if (targ == this.left_bar) {
  this.drag_mode = 'left';
 } else if (targ == this.right_bar) {
  this.drag_mode = 'right';
 } else if (targ == this.top_bar) {
  this.drag_mode = 'top';
 } else if (targ == this.bottom_bar) {
  this.drag_mode = 'bottom';
 } else {
  this.drag_mode = 'move';
 }
};

fixer.mouseup_handler = function(e) {
 this.drag_mode = 'none';
};

fixer.use_left = function() {
 this.x = this.x0;
 this.y = this.y0;
 this.w = this.h0*this.ar;
 this.h = this.h0;
 this.set_bars();
};

fixer.use_hmiddle = function() {
 this.x = this.x0 + 0.5 * (this.w0 - this.h0*this.ar);
 this.y = this.y0;
 this.w = this.h0*this.ar;
 this.h = this.h0; 
 this.set_bars();
};

fixer.use_right = function() {
 this.x = this.x0 + (this.w0 - this.h0*this.ar);
 this.y = this.y0;
 this.w = this.h0*this.ar;
 this.h = this.h0; 
 this.set_bars();
};

fixer.use_houter = function() {
 this.x = this.x0;
 this.y = this.y0 + 0.5 * this.h0 - 0.5 * this.w0/this.ar;
 this.w = this.w0;
 this.h = this.w0/this.ar;
 this.set_bars();
};

fixer.use_top = function() {
 this.x = this.x0;
 this.y = this.y0;
 this.w = this.w0;
 this.h = this.w0/this.ar;
 this.set_bars();
};

fixer.use_vmiddle = function() {
 this.x = this.x0;
 this.y = this.y0 + 0.5 * (this.h0 - this.w0/this.ar);
 this.w = this.w0;
 this.h = this.w0/this.ar; 
 this.set_bars();
};

fixer.use_bottom = function() {
 this.x = this.x0;
 this.y = this.y0 + (this.h0 - this.w0/this.ar);
 this.w = this.w0;
 this.h = this.w0/this.ar; 
 this.set_bars();
};

fixer.use_vouter = function() {
 this.x = this.x0 + 0.5 * this.w0 - 0.5 * this.h0*this.ar;
 this.y = this.y0;
 this.w = this.h0*this.ar;
 this.h = this.h0;
 this.set_bars();
};

fixer.load_image = function(i) {
 var f = this.main_form;
 f.id.value = i;
 f.command.value = 'load_image';
 f.submit();
};

fixer.apply_fix = function() {
 var f;
 f = this.main_form;
 f.crop_x.value = this.x - this.x0;
 f.crop_y.value = this.y - this.y0;
 f.crop_w.value = this.w;
 f.crop_h.value = this.h;
 f.command.value = 'apply_fix';
 f.submit();
};

fixer.keypress_handler = function(e) {
 var step = 1;
 if (e.key == "Enter") {
  this.apply_fix();
  return;
 }
 if (e.shiftKey) { step = 10; }
 if (e.ctrlKey) {
  if (this.is_fat) {
   switch (e.key) {
    case "ArrowDown" : this.use_hmiddle(); break;
    case "ArrowUp"   : this.use_houter();  break;
    case "ArrowLeft" : this.use_left();    break;
    case "ArrowRight": this.use_right();   break;
    default: return; 
   }
  } else {
   switch (e.key) {
    case "ArrowDown" : this.use_bottom();  break;
    case "ArrowUp"   : this.use_top();     break;
    case "ArrowLeft" : this.use_vmiddle(); break;
    case "ArrowRight": this.use_vouter();  break;
    default: return; 
   }
  }
 } else {
  switch (e.key) {
   case "ArrowDown":  this.move_bars(0, step,'move'); break;
   case "ArrowUp":    this.move_bars(0,-step,'move'); break;
   case "ArrowLeft":  this.move_bars(-step,0,'move'); break;
   case "ArrowRight": this.move_bars( step,0,'move'); break;
   case "+"         : this.scale(step);    break;
   case "="         : this.scale(step);    break;
   case "-"         : this.scale(-step);   break;
   case "_"         : this.scale(-step);   break;
   default: return;
  }
 }
};

fixer.wheel_handler = function(e) {
 var step = 1;
 if (e.shiftKey) { step = 10; }
 if (e.deltaY < 0) {
  this.scale(step);
 } else {
  this.scale(-step);
 }
};

fixer.delete_image = function() {
 var i = this.main_form.id.value;
 var xhr = frog.create_xhr();
 xhr.open('GET','/zoo/ajax/delete_image.php?image_id=' + i);

 try {
  xhr.send(null);
 } catch(e) {
  alert('fixer.delete_image: Could not send XMLHttpRequest object to server');
  return(null);
 }

 this.main_image.style.display = 'none';
 if (this.next_image) {
  this.load_image(this.next_image);
 } else if (this.previous_image) {
  this.load_image(this.previous_image);
 }
// window.close();
}
