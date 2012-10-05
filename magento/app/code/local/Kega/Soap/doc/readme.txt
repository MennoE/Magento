
Kega_Soap documentation
#############################

This document contains the basic know-abouts for working with the Kega_Soap module.
Although all methods are properly documentation in the code, the following might help you hand during
some common actions.

##--------------------------
## Performance improvement:
##--------------------------
By installing this module on Magento you will get better SOAP API performance.

This module adds an extra config item: Config -> Services -> Mageto Core API -> WSDL Caching.
By default the Magento Soap Server disables caching, with caching enabled the response times are much better!

* Todo: In the future it would be nice to generate WSDL files with only the methods available for the provided credentials.