<?php

//////////////////////
// Fix a given date //
//////////////////////
function FixDate($date)
{
	if (!empty($date))
	{
		$time = strtotime($date);
		return date('m/d/Y', $time);
	}

	return "";
}

///////////////////////////////////////
// Allow new style of date selection //
///////////////////////////////////////
function ChooseDate($varname, $default)
{
	global $g_count;
	if (empty($g_count))
		$g_count = 4;
	else
		$g_count--;
?>                
                              <div class="col-md-11 xdisplay_inputx form-group has-feedback">
                                <input type="text" class="form-control has-feedback-left" id="single_cal<?=$g_count?>" placeholder="Select Date" aria-describedby="inputSuccess2Status<?=$g_count?>" name="<?=$varname?>" value="<?=$default?>">
                                <span class="fa fa-calendar-o form-control-feedback left" aria-hidden="true"></span>
                                <span id="<?=$varname?>" class="sr-only">(success)</span>
                              </div>
<?php
}
?>