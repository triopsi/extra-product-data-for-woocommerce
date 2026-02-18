<?php
/**
 * Post data example.
 *
 * This file contains an example of the structure of the post data for custom fields.
 *
 * @package Extra_Product_Data_For_WooCommerce
 */

$data = array(
	'short_text' => 'Blub',
	'long_text'  => 'Bla
Bla
Blu',
	'select'     => 'C',
	'radio'      =>
	array(
		0 => 'A',
	),
	'checkbox'   =>
	array(
		0 => 'B',
	),
	'yes_no'     => 'no',
	'e_mail'     => 'local@test.de',
	'number'     => '512',
	'date'       => '2026-02-18',
);
