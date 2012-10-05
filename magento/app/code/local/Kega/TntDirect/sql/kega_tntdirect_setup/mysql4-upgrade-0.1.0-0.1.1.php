<?php
/*
 * We downgrade the shipment increment id to a length of 8 chars.
 * With a '2' as prefix.
 * In this way we can always use the increment id in the PostNL barcode (total maximum length is then 13 chars).
 */

// Define padlength, length is without the prefix character(s).
$padLength = 6;

// Define prefix character(s).
$prefixChars = 2;

$installer = $this;
$installer->startSetup();
$installer->run("
UPDATE
	eav_entity_store
INNER JOIN
	eav_entity_type
	ON
	eav_entity_type.entity_type_id = eav_entity_store.entity_type_id
SET
	eav_entity_type.increment_pad_length = {$padLength},
	eav_entity_store.increment_prefix = {$prefixChars},
	eav_entity_store.increment_last_id = CONCAT({$prefixChars},
												LPAD(
													SUBSTRING(eav_entity_store.increment_last_id, {$padLength}),
													{$padLength},
													eav_entity_type.increment_pad_char)
												)
WHERE
	eav_entity_type.entity_type_code = 'shipment';
");
$installer->endSetup();