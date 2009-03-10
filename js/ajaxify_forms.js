/*
 * This takes a form that submits to a regular location, and 'ajaxifies' it
 * meaning that the method/action is done instead as an xmlhttprequest
 */

var ajax_forms = [];

function ajaxifyForms() {
    var forms = document.getElementsByTagName('form');
    forEach (forms,function(f){return new AjaxForm(f);});
}

function AjaxForm(f) {
    this.index = ajax_forms.push(this) -1;

    this.form = f;
    this.method = f.method.toUpperCase();
    if (this.method == '') {
    this.method = 'GET';
    }
    this.headers = [];
    if (this.method == 'POST') {
    this.headers.push(["Content-Type", 'application/x-www-form-urlencoded']);
    }
    
    this.action = f.action;
    if (this.action == '') {
    this.action = document.location.href;
    }
    f.action = 'javascript:ajax_forms['+this.index+'].submit()';
}

AjaxForm.prototype.submit = function(evt) {
    var self = this;
    var querystr = queryString(self.form);
    var sendContent = (self.method == "GET") ? undefined : querystr;
    var url = (self.method == "GET") ? self.action+'?'+querystr : self.action;
    ///log(url);
    form_functions[self.form.name]['processing']();
    var def = doXHR(url, {
    'method':self.method,
    'headers':self.headers,
    'sendContent':sendContent
    } );
  
    // try to locate a form specific callback function, named <form name>_success
    // otherwise, use the default, noop callback
    callback = form_functions[self.form.name]['success'];
    //callback = bind(func_name, self);
    if (isUndefinedOrNull(callback)) {
      callback = self.success;
    }
    //Success_function = success_fuctions[this.form.id];
    def.addCallbacks(callback,self.failure);
}

AjaxForm.prototype.success = function(response) {
   //log('success!');
  alert('submit successful');
}

AjaxForm.prototype.failure = function(err) {
    //log('failed!' + err);
  alert('submit failed!');
}

form_functions= {};
form_functions['list_members'] = {
  'processing' : function() {
      //log('inside list_members processing');
    var curMessage = getElement('list_members_message');
    // swapElementClass(resultsDiv, "unknown", "inprogress");
    var newMessage = DIV({ 'class' : 'inprogress', 'id' : 'list_members_message'}, 'Processing...');
    swapDOM(curMessage, newMessage);
    //resultsDiv.appendChild(messageDiv);
  },
  'success' : function(response) {
      //log('inside list_members_success');
    var messageDiv = getElement('list_members_message');
    var results = evalJSON(response.responseText);
    var resultsTable = TABLE({'class' : 'membertable', 'id' : 'list_members_message'},
			     TBODY(null, TR(null, map(partial(TD, null), results['results']))));
    //log (results);
    swapDOM(messageDiv, resultsTable);
    Highlight(resultsTable, { delay: 0, duration: 5, startcolor : '#FFFFFF' } );
  }
};
form_functions['selfjoin'] = {
  'processing' : function() {
    log('inside selfjoin processing');
    var curMessage = getElement('selfjoin_message');
    var newMessage = DIV({ 'class' : 'inprogress', 'id' : 'selfjoin_message'}, 'Processing...');
    swapDOM(curMessage, newMessage);
  },
  'success' : function(response) {
    log('inside selfjoin success');

    var curMessage = getElement('selfjoin_message');
    var results = evalJSON(response.responseText);

    setElementClass(curMessage, 'unknown');
    curMessage.innerHTML =  results['results'];

    Highlight(curMessage, { delay: 0, duration: 5, startcolor : '#FFFFFF' } );

    // if we self-joined a new member, swap out the create button for the link to it
    if (results['joined'] != 'false') {
      var space = results['joined'];
	newCell = DIV(null, SPAN(null, "You are a member of "), A({'href' : 'http://' + space + '.wikispaces.columbia.edu' }, 'http://' + space + '.wikispaces.columbia.edu'));
      oldCell = getElement(space + '-action'); 
      swapDOM(oldCell, newCell);
    }

  }
};

form_functions['createspace'] = {
  'processing' : function() {
      //log('inside createspace processing');
    var curMessage = getElement('createspace_message');
    var newMessage = DIV({ 'class' : 'inprogress', 'id' : 'createspace_message'}, 'Processing...');
    swapDOM(curMessage, newMessage);
  },
  'success' : function(response) {
      //log('inside createspace success');
    var curMessage = getElement('createspace_message');
    var results = evalJSON(response.responseText);

    setElementClass(curMessage, 'unknown');
    curMessage.innerHTML =  results['results'];

    Highlight(curMessage, { delay: 0, duration: 5, startcolor : '#FFFFFF' } );

    
    // if we made a new wiki, swap out the create button for the link to it
    if (results['created'] != 'false') {
      var space = results['created'];
      newCell = DIV(null, A({'href' : 'http://' + space + '.wikispaces.columbia.edu' }, space + '.wikispaces.columbia.edu'));
      oldCell = getElement(space + '-action'); 
      swapDOM(oldCell, newCell);
      
      infoCell = getElement(space + '-info-link');
      showElement(infoCell);
    }
  }
};
addLoadEvent(ajaxifyForms);
