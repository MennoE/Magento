
Kega_Pdf documentation
#############################

This document contains the basic know-abouts for the Kega_Pdf module.

##-------------------------------------------
## Invoice, Creditnota, Shipment PDF's.
##-------------------------------------------
Kega_Pdf rewrites the default Magento Pdf's.
The layout of the Kega PDF's is not simillar to the default Magento PDF's,
but still uses the original Mage_Sales_Model_Order_Pdf_Abstract as base class.

##-------------------------------------------
## Refund PDF.
##-------------------------------------------
Kega_Pdf also adds a refund PDF to the standard PDF's.
This PDF can be printed from the shipping grid and view page.
* It is possible to add a Mass-action to the order grid also, but at this moment this feature is out of scope.

##-------------------------------------------
## Overall adjustments to the PDF's.
##-------------------------------------------
In the corresponding Models in the Kega_Pdf/Model/Pdf/ dir you can find the basic layout of each PDF.
The PDF items can be found in the Kega_Pdf/Model/Pdf/items dir.


##-------------------------------------------
## DispatchEvent(s).
##-------------------------------------------
At the end of the refund PDF creation, the module dispatches the following events: 
kega_pdf_refund_insert_shipment_track_stickers_before
kega_pdf_refund_insert_shipment_track_sticker_{$CarrierCode} (for every track in the shipment)
kega_pdf_refund_insert_shipment_track_stickers_after

Usage example:
Kega_TntDirect listens to event 'kega_pdf_refund_insert_shipment_track_sticker_kega_tnt_direct'
and inserts the PostNL tracking sticker into the refund PDF (page).

Kega_......... listens to event 'kega_pdf_refund_insert_shipment_track_stickers_after'
and inserts a custom instore barcode sticker into the refund PDF (page).
(on top of the PostNL tracking sticker)