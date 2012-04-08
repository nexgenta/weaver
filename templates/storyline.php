<?php

if(!isset($storylineTitle))
{
	$storylineTitle = 'Story Timeline';
}

$sldata = array();

writeLn('<h2>' . _e($storylineTitle) . '</h2>');
writeLn('<div id="storyline-data">');
writeLn('</div>');
writeLn('<ul class="storyline">');
foreach($storyLine as $obj)
{
	$date = 'Date Unknown';
	if(isset($obj->date))
	{
		$date = $obj->date;
	}
	$synopsis = '';
	if(isset($obj->shortDescription))
	{
		$synopsis = $obj->shortDescription;
	}
	if(isset($obj->thumbnail))
	{
		$style = ' style="background-image: url(\'' . _e($obj->thumbnail) . '\');"';
	}
	else
	{
		$style = '';
	}
	$slinfo = array(
		'title' => $obj->title,
		'date' => $date,
		'synopsis' => $synopsis,
		'description' => isset($obj->mediumDescription) ?  $obj->mediumDescription : (isset($obj->shortDescription) ? $obj->shortDescription : ''),
		'characters' => array(),
		);
	if(isset($obj['agents']))
	{
		foreach($obj['agents'] as $agent)
		{
			$slinfo['characters'][$agent->uuid] = array(
				'name' => $agent->title,
				'link' => $app_root . $agent->relativeURI,
				'thumb' => (isset($agent->thumb) ? $agent->thumb : ''),
				);
		}
	}
	$sldata[$obj->uuid] = $slinfo;
	writeLn('<li data-uuid="' . _e($obj->uuid) . '">' .
			'<span class="inner">' .
			'<a ' . $style . 'href="' . _e($app_root . $obj->relativeURI) . '" title="' . _e($date . ': ' . $obj->title) . '">&nbsp;</a>' .
			'</span>' .
			'</li>');
}
writeLn('</ul>');
writeLn('<script>window.storylineData = ' . json_encode($sldata) . '</script>');
