
Kega_Autoprint documentation
#############################

This document contains the basic know-abouts for the Kega_Autoprint module.

##----------------------------------------------
## Invoice, Creditnota, Shipment, Refund PDF's.
##----------------------------------------------
Kega_Autoprint redirects the output of PDF downloads to an FTP server and
outputs an ActiveX script that starts the FTP download script on the local PC.

##-----------------------------------------------------------------
## Observer: adminhtml_controller_action_prepare_download_response
##-----------------------------------------------------------------
We redirect the PDF download responses.
- Catch the content and save it to FTP server.
- Redirect the browser to ActiveX download page.
- Return to original page (or the start page).
or (if current user is not in the list of active admin/roles)
- Do nothing...

##------------------------------------------------------------------------------
## Observer: controller_action_postdispatch_adminhtml_sales_order_shipment_save
##------------------------------------------------------------------------------
When a shipment is created and saved in the admin, we want to print the PDF's automaticly.
So we upload the shipment PDF to the FTP server, output the ActiveX script
and redirect to the print refund controller / action.

To accomplish this we unset the redirect (Location) header and output the ActiveX script instead.

##-----------------------------
## ActiveX FTP download script
##-----------------------------
To make the ActiveX FTP download script work, you need to unzip the C_Kega_Autoprint.zip to C: on the local PC.
(zipfile can be found in this doc dir)

##---------------
## Autoprint PRO
##---------------
The latest version of Autoprint PRO can be downloaded from:
http://www.4-tech-engineering.com/software/autoprintpro/autoprintpro.htm

You need to buy a licence for every PC (or server).
* Reiner gives us discount, when buying them in batches of 10 licences.