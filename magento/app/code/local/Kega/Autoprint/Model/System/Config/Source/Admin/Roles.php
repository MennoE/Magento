<?php
/**
 * In the system.xml we used this model for providing the available admin role options.
 * <source_model>kega_autoprint/system_config_source_admin_roles</source_model>
 *
 */
class Kega_Autoprint_Model_System_Config_Source_Admin_Roles
{
	/**
	 * Retreive all admin roles options.
	 *
	 * @return array
	*/
	public function toOptionArray()
	{
		$rolesCollection = Mage::getModel('admin/roles')->getCollection();

		$roles = array();
		foreach ($rolesCollection as $role) {
			$roles[] = array('value' => $role->getId(), 'label' => $role->getRoleName());
		}

		return $roles;
	}
}