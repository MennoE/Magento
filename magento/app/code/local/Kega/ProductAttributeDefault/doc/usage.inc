PROFIlE SETTINGS:

NOTE: the extension also depends on app/code/local/Varien/Data (added regexp operator for sql query). This folder also has to be copied with the extension.

----- General info

- Status - only enabled profiles are run

- Is Dry Run - if yes the product is not saved in magento db (you can check the log to see if everything is ok)

- Overwrite manual product changes - if yes, will overwrite the product changes made by the user.
Example:
Product #abcd - the user manually changes the description and the categories (from Product Edit section)
The changed attributes are saved in a table (kega_productattributedefault_manual_product_changes) - the changes are saved globally
Profile #profile-1 - sets new values for description, categories (adds new categories), and product color.
If Overwrite manual product changes is set to no, then when the profile is run the description and categories attributes are skipped from updating, only the color is updated.
  
- Apply to stores - the profile only selects (rule matching) and updates products (actions) from the selected stores.



----- Conditions


The products are matched only if all the conditions are true
a condition is made from three parts
operand1, operator, operand1

- operand1 can be any of the products attributes + categories_id (this is not a product attribute) (some attributes are skipped, eg: media_gallery)
Kega_ProductAttributeDefault_Helper_Data::getProductAttributesOptions();

- operator depends on the selected operators (some operators are not valid for a certain (operand1, operand2) pair)
Kega_ProductAttributeDefault_Model_Productattributedefault::getOperatorOptions()
Kega_ProductAttributeDefault_Model_Productattributedefault::getOperatorsValidAttrCodeAttrCodeOptions()
Kega_ProductAttributeDefault_Model_Productattributedefault::getOperatorsValidAttrCodeCategoryIdOptions()

- operator2 can be a value (input text) or another attribute/day option (select)
If operator1 is a select type element (eg color) the operator2 must contain the label (admin value) of the desired value:
eg brand is 'NIKE'


The conditions are applied to all the products from the selected stores but the simple products that are linked to configurable products are skipped.


----- Actions

- add categories - the selected categories are added to the matched products:
- remove categories - the selected categories are removed from the matched products
- set attributes - fixed value
- set dynamic attributes -   
	@see Kega_ProductAttributeDefault_Helper_Data::getDynamicAttributeValue ()
	

RUN 

- after each run a log file is created (one per every profile) and it can be downloaded by clicking the 'Download log' button
 - manually:  cron/product_default_attr.php
 - automatically: a cron job is set in config.xml: set_product_default_values
 

SOME CODE NOTES:
The rules are parsing using the Kega_ProductAttributeDefault_Helper_Rule
for every operator is defined a function that adds a filter/where condition to the original sql:
public function matchIsLessThan($attributeName, $attributePattern, $productCollection)
{
	$productCollection->addFieldToFilter($attributeName, array('lt' => $attributePattern));

	return $productCollection;
}
