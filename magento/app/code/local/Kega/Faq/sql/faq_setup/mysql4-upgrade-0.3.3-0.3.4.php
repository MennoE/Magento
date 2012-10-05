<?php
$installer = $this;

$installer->startSetup();

$installer->run("
	DROP TABLE `faq_question_store_view` ;
	DROP TABLE `faq_category_store_view` ;
");
$installer->endSetup();