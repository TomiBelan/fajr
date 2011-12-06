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

