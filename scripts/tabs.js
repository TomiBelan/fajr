function Tabs() {
	this.tabs = Array();
	
	this.hideAll = function() {
		for (var i=0; i < this.tabs.length; i++) {
			this.hideTab(this.tabs[i]);
		}
	}
	
	this.showTab = function(id) {
		this.hideAll();
		var element = document.getElementById('tab_'+id);
		element.style.display = '';
		var label = document.getElementById('tab_label_'+id);
		label.className='tab_selected';
	}

	this.hideTab = function(id) {
		var element = document.getElementById('tab_'+id);
		element.style.display = 'none';
		var label = document.getElementById('tab_label_'+id);
		label.className='tab';
	}
}
