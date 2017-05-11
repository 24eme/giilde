<?php

function formatNumber($number, $round = 0) {
	return ($number && $number != 0)? number_format($number, $round, ',', '') : null;
}

function getEvol($last, $current) {
	$last = str_replace(',', '.', $last);
	$current = str_replace(',', '.', $current);
	return ($last > 0)? formatNumber((($current - $last) / $last) * 100, 2) : null;
}