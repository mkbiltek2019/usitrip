/* history and ajax back button start {*/

if (!window.unFocus) var unFocus = {};

unFocus.EventManager = function() {
	this._listeners = {};
	for (var i = 0; i < arguments.length; i++) {
		this._listeners[arguments[i]] = [];
	}
};

unFocus.EventManager.prototype = {
	addEventListener: function($name, $listener) {
		// check that listener is not in list
		for (var i = 0; i < this._listeners[$name].length; i++)
			if (this._listeners[$name][i] == $listener) return;
		// add listener to appropriate list
		this._listeners[$name].push($listener);
	},
	removeEventListener: function($name, $listener) {
		// search for the listener method
		for (var i = 0; i < this._listeners[$name].length; i++) {
			if (this._listeners[$name][i] == $listener) {
				this._listeners.splice(i,1);
				return;
			}
		}
	},
	notifyListeners: function($name, $data) {
		for (var i = 0; i < this._listeners[$name].length; i++)
			this._listeners[$name][i]($data);
	}
};


unFocus.History = (function() {

function Keeper() {
	var _this = this,
		_pollInterval = 200, _intervalID,
		_currentHash;

	var _getHash = function() {
		return location.hash.substring(1);
	};
	_currentHash = _getHash();
	

	var _setHash = function($newHash) {
		window.location.hash = $newHash;
	};
	
	
	function _watchHash() {
		var $newHash = _getHash();
		if (_currentHash != $newHash) {
			_currentHash = $newHash;
			_this.notifyListeners("historyChange", $newHash);
		}
	}
	// set the interval
	//if (setInterval) _intervalID = setInterval(_watchHash, _pollInterval);
	
	function _createAnchor($newHash) {
		if (!_checkAnchorExists($newHash)) {
			var $anchor;
			if (/MSIE/.test(navigator.userAgent) && !window.opera)
				$anchor = document.createElement('<a name="'+$newHash+'">'+$newHash+"</a>");
			else
				$anchor = document.createElement("a");
			$anchor.setAttribute("name", $newHash);
			with ($anchor.style) {
				position = "absolute";
				display = "block";
				top = getScrollY()+"px";
				left = getScrollX()+"px";
			}
			document.body.insertBefore($anchor,document.body.firstChild);
		}
	}
	function _checkAnchorExists($name) {
		if (document.getElementsByName($name).length > 0)
			return true;
	}
	if (typeof self.pageYOffset == "number") {
		function getScrollY() {
			return self.pageYOffset;
		}
	} else if (document.documentElement && document.documentElement.scrollTop) {
		function getScrollY() {
			return document.documentElement.scrollTop;
		}
	} else if (document.body) {
		function getScrollY() {
			return document.body.scrollTop;
		}
	}
	eval(String(getScrollY).toString().replace(/Top/g,"Left").replace(/Y/g,"X"));
	
	_this.getCurrent = function() {
		return _currentHash;
	};
	
	
	function addHistory($newHash) {
		if (_currentHash != $newHash) {
			_createAnchor($newHash);
			_currentHash = $newHash;
			_setHash($newHash);
			_this.notifyListeners("historyChange",$newHash);
		}
		return true;
	}
	_this.addHistory = function($newHash) { // adds history and bookmark hash
		_createAnchor(_currentHash);
		// replace with slimmer versions...
		_this.addHistory = addHistory;
		// ...do first call
		return _this.addHistory($newHash);
	};

	
	if (/WebKit\/\d+/.test(navigator.appVersion) && navigator.appVersion.match(/WebKit\/(\d+)/)[1] < 420) {
		var _unFocusHistoryLength = history.length,
			_historyStates = {}, _form,
			_recentlyAdded = false;
		
		function _createSafariSetHashForm() {
			_form = document.createElement("form");
			_form.id = "unFocusHistoryForm";
			_form.method = "get";
			document.body.insertBefore(_form,document.body.firstChild);
		}
		
		_setHash = function($newHash) {
			_historyStates[_unFocusHistoryLength] = $newHash;
			_form.action = "#" + _getHash();
			_form.submit();
		};
		
		_getHash = function() {
			return _historyStates[_unFocusHistoryLength];
		};
		
		_historyStates[_unFocusHistoryLength] = _currentHash;
		
		function addHistorySafari($newHash) {
			if (_currentHash != $newHash) {
				_createAnchor($newHash);
				_currentHash = $newHash;
				_unFocusHistoryLength = history.length+1;
				_recentlyAdded = true;
				_setHash($newHash);
				_this.notifyListeners("historyChange",$newHash);
				_recentlyAdded = false;
			}
			return true;
		}
		
		_this.addHistory = function($newHash) { // adds history and bookmark hash
			_createAnchor(_currentHash);
			_createSafariSetHashForm();
			
			_this.addHistory = addHistorySafari;
			
			return _this.addHistory($newHash);
		};
		function _watchHistoryLength() {
			if (!_recentlyAdded) {
				var _historyLength = history.length;
				if (_historyLength != _unFocusHistoryLength) {
					_unFocusHistoryLength = _historyLength;
					
					var $newHash = _getHash();
					if (_currentHash != $newHash) {
						_currentHash = $newHash;
						_this.notifyListeners("historyChange", $newHash);
					}
				}
			}
		};
		
		//clearInterval(_intervalID);
		//_intervalID = setInterval(_watchHistoryLength, _pollInterval);
		
	} else if (typeof ActiveXObject != "undefined" && window.print && 
			   !window.opera && navigator.userAgent.match(/MSIE (\d\.\d)/)[1] >= 5.5) {
		var _historyFrameObj, _historyFrameRef;
		

		function _createHistoryFrame() {
			var $historyFrameName = "unFocusHistoryFrame";
			_historyFrameObj = document.createElement("iframe");
			_historyFrameObj.setAttribute("name", $historyFrameName);
			_historyFrameObj.setAttribute("id", $historyFrameName);
			_historyFrameObj.setAttribute("src", 'javascript:;');
			_historyFrameObj.style.position = "absolute";
			_historyFrameObj.style.top = "-900px";
			document.body.insertBefore(_historyFrameObj,document.body.firstChild);
			
			_historyFrameRef = frames[$historyFrameName];
			
			_createHistoryHTML(_currentHash, true);
		}
		
	
		function _createHistoryHTML($newHash) {
			with (_historyFrameRef.document) {
				open("text/html");
				write("<html><head></head><body onl",
					'oad="parent.unFocus.History._updateFromHistory(\''+$newHash+'\');">',
					$newHash+"</body></html>");
				close();
			}
		}
		
		
		function updateFromHistory($hash) {
			_currentHash = $hash;
			_this.notifyListeners("historyChange", $hash);
		}
		_this._updateFromHistory = function() {
			_this._updateFromHistory = updateFromHistory;
		};
			function addHistoryIE($newHash) { 
				if (_currentHash != $newHash) {
					_currentHash = $newHash;
					_createHistoryHTML($newHash);
				}
				return true;
			};
			_this.addHistory = function($newHash) {
				_createHistoryFrame();
				
				_this.addHistory = addHistoryIE;
				return _this.addHistory($newHash);
			};
			_this.addEventListener("historyChange", function($hash) { _setHash($hash) });
	
	}
}
Keeper.prototype = new unFocus.EventManager("historyChange");

return new Keeper();

})();

///////////////�÷�//////////////////////
//hashString = '123=789&abc=456';
//unFocus.History.addHistory(hashString);

/* history and ajax back button end }*/