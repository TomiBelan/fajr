function toggleVisibility(id) {
		// OK, kedze je includnuty prototype, mozme ho pouzit
		var element = $(id);
		if (!element.hasClassName('collapsed') ) {
				element.addClassName('collapsed');
		} else {
				element.removeClassName('collapsed');
		}
		
}

document.observe('dom:loaded', function() {
  $$('.collapsible').each(function (div) {
    var header = div.down('.collapsibleheader h2');
    if (!header) {
      // Vo fajri asi vsade mame aj h2
      // ale pre kazdy pripad chceme vzdy mat link v collapsibleheader
      header = div.down('.collapsibleheader');
    }
    var inner = header.innerHTML;
    header.innerHTML = '';
    var link = document.createElement('a');
    link.innerHTML = inner;
    link.setAttribute('href', '#');
    link.onclick = function() {
      toggleVisibility(div);
      return false;
    };
    header.appendChild(link);
  });
});

document.observe('dom:loaded', function() {
  $$('.studium-a-zapisny-list').each(function (div) {
    var active = false;
    var p = document.getElementById('p_'+div.id);
    p.innerHTML += '<a href="#" class="togglovac">Zmeniť/Viac info</a>';
    var link = p.getElementsByTagName('a')[0];
    link.onclick = function () {
      active = !active;
      div.style.display = (active ? 'block' : 'none');
      link.innerHTML = (active ? 'Skryť' : 'Zmeniť/Viac info');
      return false;
    };
  });
});

