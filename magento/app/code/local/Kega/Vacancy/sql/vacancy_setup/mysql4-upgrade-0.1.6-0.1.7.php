<?php

$installer = $this;
$installer->startSetup();
$installer->run("

INSERT INTO vacancyregion(title, status, sequence) VALUES('Nederland', 1, 10);
INSERT INTO vacancyregion(title, status, sequence) VALUES('Noord', 1, 20);
INSERT INTO vacancyregion(title, status, sequence) VALUES('Midden', 1, 30);
INSERT INTO vacancyregion(title, status, sequence) VALUES('Zuid', 1, 40);

");
$installer->endSetup();