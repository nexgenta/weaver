var Storyline = {};
Storyline.init = function() {
	var $this = this;
	this.last = null;
	this.data = window.storylineData;
	$('.storyline li').hover(function() { $this.hover($(this)); });
	this.hover($('.storyline li').first());
}
Storyline.esc = function(s)
{
	return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
Storyline.hover = function(me)
{
	var data = this.data[me.attr('data-uuid')], c;
	
	if(typeof data == 'undefined')
		{
			return;
		}
	me.addClass('hover');
	$('#storyline-data').replaceWith('<div id="storyline-data"><h3>' + this.esc(data.title) + '</h3><p class="summary">' + this.esc(data.synopsis) + '</p><p class="date">' + this.esc(data.date) + '</p></div>');
	if(this.last && this.last != me[0])
		{
			$(this.last).removeClass('hover');
		}
	this.last = me[0];
	if((c = $('#sel-story-desc')))
		{
			c.replaceWith('<div id="sel-story-desc"><h2>Event Synopsis</h2><p>' + this.esc(data.description) + '</p></div>');
		}
	console.log(data.characters);
	if((c = $('#sel-story-characters')))
		{
			var h = [], n = 0;
			h.push('<div id="sel-story-characters"><h2>Characters</h2><ul>');
			for(var i in data.characters)
			{
				h.push('<li><a href="' + data.characters[i].link + '">' + this.esc(data.characters[i].name) + '</a></li>');
				n++;
				if(n == 5)
					{
						break;
					}
			}
			h.push('</ul></div>');
			c.replaceWith(h.join(''));
		}
}

Storyline.init();

