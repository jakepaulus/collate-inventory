/* This is a script taken from  http://www.quirksmode.org/js/options.html with script.aculo.us effects added. */

var store = new Array();

store[0] = new Array(
	'location', 'location');

store[1] = new Array(
	'user', 'username',
	'location', 'site',
	'software', 'software');
	
store[2] = new Array(
	'user', 'username',
	'level', 'level',
	'message', 'message');

function init()
{
	optionTest = true;
	lgth = document.forms[0].second.options.length - 1;
	document.forms[0].second.options[lgth] = null;
	if (document.forms[0].second.options[lgth]) optionTest = false;
}


function populate()
{
	if (!optionTest) return;
	var box = document.forms[0].first;
	var number = box.options[box.selectedIndex].value;
	if (!number) return;
	var list = store[number];
	var box2 = document.forms[0].second;
	box2.options.length = 0;
	for(i=0;i<list.length;i+=2)
	{
		box2.options[i/2] = new Option(list[i],list[i+1]);
	}
	if (number == 0)
	{
		new Effect.Fade('extraforms', {duration: 0.2});
	}
	else if (number == 1)
	{
		new Effect.Appear('extraforms', {duration: 0.2});
		new Effect.Appear('extraforms2', {duration: 0.2});
	}
	else if (number == 2)
	{
		new Effect.Fade('extraforms2', {duration: 0.2});
		new Effect.Appear('extraforms', {duration: 0.2});
	}
}