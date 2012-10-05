
Kega_UrapidFlow documentation
#############################

This document contains the basic know-abouts for working with the Kega_UrapidFlow module.
Although all methods are properly documentation in the code, the following might help you hand during
some common actions.

## ADDING A NEW ATTRIBUTE
To import a newly create attribute we need to go through the following steps:

1. Create the attribute in the admin and remember the systemcode you gave it
2. Add the attribute to the attribute set in question, mostly this is 'Default'

3. Add the the attribute systemcode to the following places in the code

Kega_URapidFlow_Model_Product
$processedCsvHeaders - these headers are being used as CSV headers in the product_import.txt
$processedCsvHeadersUpdate - these headers are being used as CSV headers in the product_update.txt

Kega_URapidFlow_Model_Product_Parse_ Pfa.php or Other.php
Add the attribute code to the mapping array's in the following methods:
getConfigurableProductData()
getSimpleProductData()


## Abstract
Every parser now uses the Kega_URapidFlow_Model_Parse_Abstract, for example we now have:
Kega_URapidFlow_Model_Product_Parse_Pfa.php
Kega_URapidFlow_Model_Price_Parse_Pfa.php
Kega_URapidFlow_Model_Stock_Parse_Pfa.php

If you need another kind of connector, create your own parsers in the Parse dir's of Product, Price and Stock.
Do not forget to adjust loading of the parser model in the Product and Stock Observers.

Kega_UrapidFlow future development
##################################
Maybe add more abstraction, so less code has to be adjusted / reviewed when creating an import for a new customer.

Currently the speed of the module is good enough.
Though if we need to increase the performance a step further:

During the price rebuild we noticed that the CSV file that is imported also contains all simple products.
Since the rebuild is only for configurables, we dont need to re-import simple prices.
If we make this change, we should be able to shave of a few minutens per price rebuild import.

