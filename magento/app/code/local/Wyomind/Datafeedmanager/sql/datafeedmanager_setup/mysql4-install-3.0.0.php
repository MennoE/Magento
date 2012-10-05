<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('datafeedmanager')};
 ");


$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('datafeedmanager')}` (
  `feed_id` int(11) NOT NULL auto_increment,
  `feed_name` varchar(20) NOT NULL,
  `feed_type` tinyint(3) NOT NULL,
  `feed_path` varchar(255) NOT NULL default '/',
  `feed_status` int(1) NOT NULL default '0',
  `feed_updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `store_id` int(2) NOT NULL default '1',
  `feed_include_header` int(1) NOT NULL default '0',
  `feed_header` text,
  `feed_product` text,
  `feed_footer` text,
  `feed_separator` char(3) default NULL,
  `feed_protector` char(1) default NULL,
  `feed_required_fields` text,
  `feed_enclose_data` int(1) NOT NULL default '1',
  `datafeedmanager_categories` longtext,
  `datafeedmanager_type_ids` varchar(150) default NULL,
  `datafeedmanager_visibility` varchar(10) default NULL,
  `datafeedmanager_attributes` text,
  `cron_expr` varchar(100) default '0 4 * * *',
  PRIMARY KEY  (`feed_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
");

if($_SERVER['HTTP_HOST']=="wyomind.com")
 $installer->run("UPDATE `{$this->getTable('datafeedmanager')}` SET datafeedmanager_categories ='[{\"line\": \"1/3\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/10\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/10/22\", \"checked\": false, \"mapping\": \"Furniture > Living Room Furniture\"}, {\"line\": \"1/3/10/23\", \"checked\": false, \"mapping\": \"Furniture > Bedroom Furniture\"}, {\"line\": \"1/3/13\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/13/12\", \"checked\": false, \"mapping\": \"Cameras & Optics\"}, {\"line\": \"1/3/13/12/25\", \"checked\": false, \"mapping\": \"Cameras & Optics > Camera & Optic Accessories\"}, {\"line\": \"1/3/13/12/26\", \"checked\": false, \"mapping\": \"Cameras & Optics > Cameras > Digital Cameras\"}, {\"line\": \"1/3/13/15\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/13/15/27\", \"checked\": false, \"mapping\": \"Electronics > Computers > Desktop Computers\"}, {\"line\": \"1/3/13/15/28\", \"checked\": false, \"mapping\": \"Electronics > Computers > Desktop Computers\"}, {\"line\": \"1/3/13/15/29\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/30\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/31\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/32\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/33\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/34\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/8\", \"checked\": false, \"mapping\": \"Electronics > Communications > Telephony > Mobile Phones\"}, {\"line\": \"1/3/18\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/18/19\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Clothing > Activewear > Sweatshirts\"}, {\"line\": \"1/3/18/24\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Clothing > Pants\"}, {\"line\": \"1/3/18/4\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Clothing > Tops > Shirts\"}, {\"line\": \"1/3/18/5\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Shoes\"}, {\"line\": \"1/3/18/5/16\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Shoes\"}, {\"line\": \"1/3/18/5/17\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Shoes\"}, {\"line\": \"1/3/20\", \"checked\": false, \"mapping\": \"\"}]'");


$installer->endSetup();