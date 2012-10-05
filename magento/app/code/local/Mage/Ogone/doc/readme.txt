Mage_Ogone documentation
#############################

This document contains the basic know-abouts for the Mage_Ogone module.

## Ogone payments - frontend
...todo...

## Ogone refunds - backend (AFU)
When a creditmemo is created it gets the status refund requested.
If you want to refund the creditmemo you can select it by mass update and choose for Export to Ogone.
You can also do this for one creditmemo by opening a creditmemo and clicking on the button Refund.

To let this module work, you have to fill in the AFU-API user and password in the config of the Ogone tab.
You can simply create such a user by logging in to the admin panel of Ogone.
* At Ogone they have to activate AFU Refunds and also the module Partial refunds!

When refunds are exported to Ogone this is done by a web API request with a ; data seperated file.
Requests and responses are logged in ogoneafu.log


!!!
 The refund part is copied from 4 different modules (Anda legacy code) by Tim Honders
 and is simplyfied and moved to Mage_Ogone by Mike Weerdenburg.
!!!