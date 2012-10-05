<?php

class Kega_Init_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
	/**
	* @return array
	*/
	public function getDefaultEntities()
	{
		return array(
			'catalog_category' => array(
				'entity_model'		=> 'catalog/category',
				'attribute_model'	=> 'catalog/resource_eav_attribute',
				'table'				=> 'catalog/category',
				'attributes'=> array(
					'redirect_url' => array(
                        'type'              => 'varchar',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Redirect URL',
                        'input'             => 'text',
                        'class'             => '',
                        'source'            => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => false,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                    ),
				)
			)
		);
	}
}