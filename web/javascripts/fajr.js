var clickable = true;

document.getElementsByTagName('html')[0].className = 'with-js';

document.observe('dom:loaded', function() {
	$$('table tbody tr td a').each(function (link){
		var target = link.href;
		link.up('tr').addClassName('clickable').observe('click', function(href, e){
			if (clickable)
			{
				clickable = false;
				$$('table tbody tr.clickable').invoke('removeClassName', 'clickable');
				document.location.href = href;
			}
		}.curry(target));
	});
});
