
Kega_Retargeting Module
#############################

This module is used for creating dynamic retargeting pixels.

##-------------------------------------------
## Create a pixel
##-------------------------------------------
Create a model with the method getPixel() and set the model name in the
$pixels array in the observer.

Now the returned code of getPixel() is set in the register together with
the other retargeting pixels.
This registry is echoed in the template retargeting/after_body_starts.phtml

##-------------------------------------------
## Example
##-------------------------------------------
See Kega_Retargeting_Model_Example for an example of a pixel.
Set 'example' as a value in the $pixels array in the observer to see how it works.

##-------------------------------------------
## Enable pixels through storeconfig
##-------------------------------------------
See Example pixel in system.xml, add a section of your pixel here.
Conditionally array_push the pixel into the @pixels array in the Observer.php model
(condition = store config enable setting in config)

