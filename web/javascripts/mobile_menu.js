function showMenu()
{
  $$('.tab_header')[0].show();
  $$('.tab_content')[0].hide();
  $$('h1')[0].innerHTML = '<a class="logo" href="#">FAJR</a>';
}

document.observe('dom:loaded', function() {  
  var back_button = '<a href="Javascript:void(0);" class="back_button" onclick="showMenu();">&nbsp;</a>'
  $$('h1')[0].innerHTML = back_button + $$('h1')[0].innerHTML;
  $$('.tab_header')[0].hide();  
});