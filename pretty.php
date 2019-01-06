<?php

function prettyDate($date, $additional = [], $custom_message = []){
	if(!is_array($additional)) $additional = [];
	
	if(array_key_exists('timezone', $additional))
	    $timezone = $additional['timezone'];
	else $timezone = "Europe/Paris";
	
	if(is_array($date)){
		if(array_key_exists(0, $date) && array_key_exists(1, $date)){
			$start = date_create($date[0], timezone_open($timezone));
			$end   = date_create($date[1], timezone_open($timezone));
		}else return false;
	}else return false;
	
	$message = [
		'finished'    => 'Événement terminé',
		'live'        => 'En cours',
		'until'       => ', jusqu\'au %d %s',
		'moreyear'    => 'Dans %d ans',
		'more_smonth' => 'Dans plus de 6 mois',
		'smonth'      => 'Dans %d mois',
		'month'       => 'Dans 1 mois',
		'evening'     => 'Ce soir, à %dh',
		'afternoon'   => 'Cette après-midi, à %dh',
		'noon'        => 'Ce midi, à %dh',
		'morning'     => 'Ce matin, à %dh',
		'todaytime'   => 'Aujourd\'hui, à %dh',
		'hour'        => 'Débute dans 1h',
		'30minutes'   => 'Débute dans 30 minutes',
		'20minutes'   => 'Débute dans 20 minutes',
		'15minutes'   => 'Débute dans 15 minutes',
		'fewminutes'  => 'Débute dans quelques minutes',
		'nextweek'    => '%s prochain à %dh',
		'nextweekb'   => '%s, à %dh',
		'tomorrow'    => 'Demain, à %dh',
		'inweek'      => 'Ce %s, à %dh',
		'aday'        => 'Un %s, à %dh'
	];
	
	if(is_array($custom_message))
		$message = array_replace($message, $custom_message);
	
	if(isset($additional['wrap_start']) && isset($additional['wrap_end'])){
		$new_message = [];
		foreach($message as $key => $value)
			$new_message[$key] = $additional['wrap_start'] . $value . $additional['wrap_end'];
		$message = array_replace($message, $new_message);
	}
		
	$current = date_create("2019-01-04 01:12:00", timezone_open($timezone));
	
	$current_time = $current->format('Hi');
	$start_time = $start->format('Hi');
	$end_time = $end->format('Hi');
	
	$diff = date_diff($current, $start);
	$nb_days = date_diff($start, $end);
    $nb_days = $nb_days->d + 1;
	
	if($current->format('Ymd') >= $end->format('Ymd') && $current_time > $end_time)
		$fdate = $message['finished'];
	elseif($current->format('Ymd') >= $start->format('Ymd') && $current->format('Ymd') <= $end->format('Ymd')){ // in event
            if(($current->format('dHi') >= $start->format('dHi')) && $current_time <= $end_time){ // live
				$fdate = $message['live'];
				if($nb_days > 2) $fdate = $fdate . sprintf($message['until'], $end->format('d'), $end->format('F'));
			}
			elseif($diff->h == 0 && $diff->i <= 10) // few minutes ?
				$fdate = $message['fewminutes'];
			elseif($diff->h == 0 && $diff->i <= 15) // 15 minutes ?
				$fdate = $message['15minutes'];
			elseif($diff->h == 0 && $diff->i <= 20) // 20 minutes ?
				$fdate = $message['20minutes'];
			elseif($diff->h == 0 && $diff->i <= 35) // 30 minutes (flexible 35min) ?
				$fdate = $message['30minutes'];
			elseif(($diff->h == 0 && $diff->i > 35) || ($diff->h == 1 && $diff->i <= 20)) // 1 hour (modulable 35min - 1h20) ?
				$fdate = $message['hour'];
			elseif($start_time >= 1900) // evening ?
				$fdate = sprintf($message['evening'], $start->format('H'));
			elseif($start_time >= 1400 && $start_time < 1700) // afternoon ?
				$fdate = sprintf($message['afternoon'], $start->format('H'));
			elseif($start_time >= 1200) // noon ?
				$fdate = sprintf($message['noon'], $start->format('H'));
            /*elseif($start_time >= 0900) //morning
				$fdate = sprintf($message['morning'], $start->format('H'));*/
			else 
				$fdate = sprintf($message['todaytime'], $start->format('H'));
		}elseif($diff->d <= 1) //tomorrow
			$fdate = sprintf($message['tomorrow'], $start->format('H'));
		elseif($diff->d <= 7) { //next week
			switch($current->format('N')){ //changer avec start et en dessous current
		    	case 1: if($start->format('N') == 1) $fdate = sprintf($message['nextweek'], $start->format('l'), $start->format('H')); else $fdate = sprintf($message['inweek'], $start->format('l'), $start->format('H')); break;
		    	case 2: if($start->format('N') <= 2) $fdate = sprintf($message['nextweek'], $start->format('l'), $start->format('H')); else $fdate = sprintf($message['inweek'], $start->format('l'), $start->format('H')); break;
		    	case 3: if($start->format('N') <= 3) $fdate = sprintf($message['nextweek'], $start->format('l'), $start->format('H')); else $fdate = sprintf($message['inweek'], $start->format('l'), $start->format('H')); break;
		    	case 4: if($start->format('N') <= 4) $fdate = sprintf($message['nextweek'], $start->format('l'), $start->format('H')); else $fdate = sprintf($message['inweek'], $start->format('l'), $start->format('H')); break;
		    	case 5: if($start->format('N') <= 5) $fdate = sprintf($message['nextweek'], $start->format('l'), $start->format('H')); else $fdate = sprintf($message['inweek'], $start->format('l'), $start->format('H')); break;
				case 6: if($start->format('N') <= 7) $fdate = sprintf($message['nextweek'], $start->format('l'), $start->format('H')); else $fdate = sprintf($message['inweek'], $start->format('l'), $start->format('H')); break;
				case 7: $fdate = sprintf($message['nextweekb'], $start->format('l'), $start->format('H')); break;
				default: $fdate = sprintf($message['inweek'], $start->format('l'), $start->format('H')); break;
		    }
		}
	    elseif($diff->y > 1) $fdate = sprintf($message['moreyear'], $diff->y);
	    elseif($diff->y == 1) $fdate = $message['year'];
	    elseif($diff->m > 6) $fdate = $message['more_smonth'];
	    elseif($diff->m <= 6 && $diff->m > 1) $fdate = sprintf($message['smonth'], $diff->m);
	    elseif($diff->m == 1) $fdate = $message['month'];
    	else $fdate = sprintf($message['aday'], $start->format('l'), $start->format('H'));
	
	return $fdate;
}
