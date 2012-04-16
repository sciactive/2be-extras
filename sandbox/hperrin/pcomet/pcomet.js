/*
 * Pines Comet (pcomet) 0.0pre-alpha
 *
 * http://pinesframework.org/
 * Copyright (c) 2012 Hunter Perrin
 *
 * Triple license under the GPL, LGPL, and MPL:
 *	  http://www.gnu.org/licenses/gpl.html
 *	  http://www.gnu.org/licenses/lgpl.html
 *	  http://www.mozilla.org/MPL/MPL-1.1.html
 */


/** PrivateFunction: Function.prototype.bind
* Bind a function to an instance.
*
* This Function object extension method creates a bound method similar
* to those in Python. This means that the 'this' object will point
* to the instance you want. See
* <a href='https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Function/bind'>MDC's bind() documentation</a> and
* <a href='http://benjamin.smedbergs.us/blog/2007-01-03/bound-functions-and-function-imports-in-javascript/'>Bound Functions and Function Imports in JavaScript</a>
* for a complete explanation.
*
* This extension already exists in some browsers (namely, Firefox 3), but
* we provide it to support those that don't.
*
* Parameters:
* (Object) obj - The object that will become 'this' in the bound function.
* (Object) argN - An option argument that will be prepended to the
* arguments given for the function call
*
* Returns:
* The bound function.
*/
if (!Function.prototype.bind) {
    Function.prototype.bind = function (obj /*, arg1, arg2, ... */)
    {
        var func = this;
        var _slice = Array.prototype.slice;
        var _concat = Array.prototype.concat;
        var _args = _slice.call(arguments, 1);

        return function () {
            return func.apply(obj ? obj : this,
                              _concat.call(_args,
                                           _slice.call(arguments, 0)));
        };
    };
}

(function(){
	if (window.pcomet)
		return;
	pcomet = function(url, options){
		var self = this;
		if (options)
			for (var name in options)
				this.options[name] = options[name];
		this.url = url;

		// Make a new XHR object.
		if (window.XMLHttpRequest) // code for IE7+, Firefox, Chrome, Opera, Safari
			this.xhr = new XMLHttpRequest();
		else // code for IE6, IE5
			this.xhr = new ActiveXObject("Microsoft.XMLHTTP");

		this.xhr.onreadystatechange = function(){
			if (self.xhr.readyState==1) {
				//self.xhr.setRequestHeader("X-Something", 'somevalue');
				self.xhr.send("[[STREAMBEGIN]]");
			} else if (self.xhr.readyState==2) {
				// Find the begin mark and end mark headers.
				self.serverBeginMark = self.xhr.getResponseHeader("X-Begin-Mark");
				self.serverEndMark = self.xhr.getResponseHeader("X-End-Mark");
			} else if (self.xhr.readyState==3 || (self.xhr.readyState==4 && self.xhr.status==200)) {
				var allData = self.xhr.responseText,
					newData = allData.substring(self._rlength),
					rebeg = self._regExpEscape(self.serverBeginMark),
					reend = self._regExpEscape(self.serverEndMark),
					re = new RegExp(rebeg+'([\\s\\S]*?)'+reend), // Regex to find the data between the markers.
					body;
				self._rlength = allData.length;
				// For testing.
				console.log(self._rlength);
				while (re.test(newData)) {
					body = re.exec(newData);
					if (body[0])
						body = body[0];
					else
						body = String(body);
					self._callHandlers(self.state.RECEIVED, body.replace(re, "$1"));
					newData = newData.substring(body.length);
				}
			}
			if (self.xhr.readyState==4) {
				self._callHandlers(self.state.CLOSED);
				self._rlength = 0;
			}
		}

		this.xhr.timeout = 0;
		this.xhr.open(this.options.method, this.url, true);
	};
	pcomet.prototype = {
		xhr: null,
		options: {
			method: "POST" // HTTP request method.
		},
		state: {
			RECEIVED: 0, // Received a complete data body.
			SENT: 1, // Sent a complete data body.
			CLOSED: 3 // The connection has been closed.
		},
		_rlength: 0, // Length of the data already received.
		_handlers: [],
		addHandler: function(handler){
			this._handlers.push(handler);
		},
		removeHandler: function(handler){
			for (var i=0; i<this._handlers.length; i++) {
				if (this._handlers[i] === handler) {
					this._handlers.splice(i, 1);
					i--;
				}
			}
		},
		send: function(data){
//			if (this.xhr.readyState!=1)
//				this.xhr.open(this.options.method, this.url, true);
//			if (typeof data == "undefined")
//				this.xhr.send();
//			else
//				this.xhr.send(data);
			// TODO: Get rid of jQuery dependency.
			$.ajax({
				type: "POST",
				url: "http://localhost:20000",
				data: {hello: data}
			});
		},
		_callHandlers: function(state, response){
			for (var i=0; i<this._handlers.length; i++) {
				this._handlers[i].apply(this, [state, response]);
			}
		},
		_regExpEscape: function(text) {
			return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
		}
	};
})();